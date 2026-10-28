<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\InventoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicCatalogController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    /**
     * Página de Inicio pública (Vitrina Comercial).
     */
    public function home(): View
    {
        $featuredProducts = Product::published()
            ->where('is_featured', true)
            ->with(['brand:id,name', 'category:id,name', 'images'])
            ->latest('id')
            ->limit(8)
            ->get();

        $newProducts = Product::published()
            ->where('is_new', true)
            ->with(['brand:id,name', 'category:id,name', 'images'])
            ->latest('id')
            ->limit(6)
            ->get();

        $categories = Category::withCount(['products' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->limit(6)
            ->get();

        $brands = Brand::withCount(['products' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();

        return view('public.home', compact('featuredProducts', 'newProducts', 'categories', 'brands'));
    }

    /**
     * Catálogo general de productos con filtros facetados.
     */
    public function index(Request $request): View
    {
        $query = Product::published()
            ->with(['brand:id,name', 'category:id,name', 'images', 'inventoryBalances.warehouse:id,name,code']);

        // Búsqueda por texto (nombre, SKU o barcode)
        if ($request->filled('q')) {
            $query->search($request->q);
        }

        // Filtro por categoría
        if ($request->filled('categoria')) {
            $category = Category::where('slug', $request->categoria)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Filtro por marca
        if ($request->filled('marca')) {
            $brand = Brand::where('slug', $request->marca)->first();
            if ($brand) {
                $query->where('brand_id', $brand->id);
            }
        }

        // Rango de precio
        if ($request->filled('min_precio')) {
            $query->where('price', '>=', (float) $request->min_precio);
        }
        if ($request->filled('max_precio')) {
            $query->where('price', '<=', (float) $request->max_precio);
        }

        // Filtro solo productos en stock disponible
        if ($request->boolean('en_stock')) {
            $query->whereHas('inventoryBalances', fn ($q) => $q->where('qty_available', '>', 0));
        }

        // Ordenamiento
        match ($request->get('orden', 'novedades')) {
            'precio_bajo' => $query->orderBy('price', 'asc'),
            'precio_alto' => $query->orderBy('price', 'desc'),
            'nombre_asc' => $query->orderBy('name', 'asc'),
            'novedades' => $query->latest('id'),
            default => $query->latest('id'),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::withCount(['products' => fn ($q) => $q->published()])->get();
        $brands = Brand::withCount(['products' => fn ($q) => $q->published()])->get();

        return view('public.catalog', compact('products', 'categories', 'brands'));
    }

    /**
     * Ficha técnica y detalle individual de la laptop.
     */
    public function show(string $slug): View
    {
        $product = Product::published()
            ->where('slug', $slug)
            ->with([
                'brand:id,name',
                'category:id,name',
                'model:id,name',
                'images',
                'inventoryBalances.warehouse:id,name,code',
            ])
            ->firstOrFail();

        $globalStock = $this->inventoryService->getGlobalStock($product->id);

        // Laptops relacionadas de la misma categoría o marca
        $relatedProducts = Product::published()
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('category_id', $product->category_id)
                    ->orWhere('brand_id', $product->brand_id);
            })
            ->with(['brand:id,name', 'category:id,name', 'images'])
            ->limit(4)
            ->get();

        return view('public.product_detail', compact('product', 'globalStock', 'relatedProducts'));
    }

    /**
     * Descarga la ficha técnica de un producto publicado.
     */
    public function downloadTechnicalSheet(string $slug): Response
    {
        $product = Product::published()
            ->where('slug', $slug)
            ->with(['brand:id,name', 'category:id,name', 'images'])
            ->firstOrFail();

        return Pdf::loadView('pdf.product-sheet', compact('product'))
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true)
            ->download('ficha-tecnica-'.$product->sku.'.pdf');
    }

    /**
     * Página informativa: Sobre nosotros.
     */
    public function about(): View
    {
        return view('public.about');
    }

    /**
     * Página informativa: Contacto y Ubicación en Lima.
     */
    public function contact(): View
    {
        return view('public.contact');
    }
}
