<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportProductsRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Listado administrativo de productos con filtros y paginación.
     */
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'brand', 'model', 'images']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $filters = $request->only(['category_id', 'brand_id', 'status', 'min_price', 'max_price']);
        $query->filter($filters);

        $products = $query->latest('id')->paginate(12)->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total' => Product::count(),
            'published' => Product::where('status', 'published')->count(),
            'draft' => Product::where('status', 'draft')->count(),
            'archived' => Product::where('status', 'archived')->count(),
        ];

        return view('admin.products.index', compact('products', 'categories', 'brands', 'stats'));
    }

    /**
     * Formulario para crear un nuevo producto.
     */
    public function create(GeminiService $geminiService): View
    {
        $categories = Category::with('subcategories')->where('is_active', true)->orderBy('name')->get();
        $brands = Brand::with('models')->where('is_active', true)->orderBy('name')->get();
        $hasGeminiKey = $geminiService->hasApiKey();

        return view('admin.products.create', compact('categories', 'brands', 'hasGeminiKey'));
    }

    /**
     * Almacena un nuevo producto y su galería en el servidor.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Procesar especificaciones técnicas estructuradas
            if (! empty($data['technical_specs'])) {
                $cleanSpecs = array_filter($data['technical_specs'], fn ($v) => ! is_null($v) && $v !== '');
                $data['technical_specs'] = $cleanSpecs;
            }

            $product = Product::create($data);

            $this->storeProductImages($request, $product);

            AuditLog::log('product_created', 'products', $product->id, null, [
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => $product->price,
                'status' => $product->status,
            ]);

            DB::commit();

            return redirect()->route('admin.products.show', $product)
                ->with('status', 'Producto creado exitosamente con ficha técnica e imágenes validadas.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors([
                'error' => 'Error al guardar el producto: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Muestra el detalle del producto, especificaciones completas y galería.
     */
    public function show(Product $product): View
    {
        $product->load(['category', 'subcategory', 'brand', 'model', 'images']);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Formulario de edición de producto.
     */
    public function edit(Product $product, GeminiService $geminiService): View
    {
        $product->load(['images']);
        $categories = Category::with('subcategories')->where('is_active', true)->orderBy('name')->get();
        $brands = Brand::with('models')->where('is_active', true)->orderBy('name')->get();
        $hasGeminiKey = $geminiService->hasApiKey();

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'hasGeminiKey'));
    }

    /**
     * Actualiza el producto y agrega imágenes adicionales si se suben.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $oldValues = $product->only(['sku', 'name', 'price', 'status', 'category_id', 'brand_id']);

            if (! empty($data['technical_specs'])) {
                $cleanSpecs = array_filter($data['technical_specs'], fn ($v) => ! is_null($v) && $v !== '');
                $data['technical_specs'] = $cleanSpecs;
            }

            $product->update($data);

            $this->storeProductImages($request, $product);

            AuditLog::log('product_updated', 'products', $product->id, $oldValues, [
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => $product->price,
                'status' => $product->status,
            ]);

            DB::commit();

            return redirect()->route('admin.products.show', $product)
                ->with('status', 'Producto actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors([
                'error' => 'Error al actualizar el producto: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Cambiar estado rápidamente (publicar, despublicar, archivar).
     */
    public function toggleStatus(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:draft,published,archived'],
        ]);

        $oldStatus = $product->status;
        $product->update(['status' => $request->status]);

        AuditLog::log('product_status_changed', 'products', $product->id, ['status' => $oldStatus], ['status' => $product->status]);

        return back()->with('status', "Estado del producto cambiado a '{$product->status}'.");
    }

    /**
     * Eliminar una imagen de la galería.
     */
    public function deleteImage(Product $product, ProductImage $image): RedirectResponse
    {
        if ($image->product_id !== $product->id) {
            abort(403);
        }

        if (Storage::disk('public')->exists($image->file_path)) {
            Storage::disk('public')->delete($image->file_path);
        }

        $image->delete();

        // Si la imagen eliminada era la principal, asignar la primera restante como principal
        if ($image->is_primary) {
            $first = $product->images()->first();
            if ($first) {
                $first->update(['is_primary' => true]);
            }
        }

        AuditLog::log('product_image_deleted', 'products', $product->id, null, ['image_id' => $image->id]);

        return back()->with('status', 'Imagen eliminada de la galería.');
    }

    /**
     * Archivar o eliminar producto.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $sku = $product->sku;
        $product->update(['status' => 'archived']);

        AuditLog::log('product_archived', 'products', $product->id, ['status' => 'active'], ['status' => 'archived']);

        return redirect()->route('admin.products.index')
            ->with('status', "El producto [{$sku}] ha sido archivado. Ya no será visible en el catálogo público.");
    }

    /**
     * Realiza acciones masivas sobre los productos seleccionados (eliminar definitivamente o archivar).
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:delete,archive,publish',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
            'select_all_pages' => 'nullable',
        ]);

        $action = $request->input('action');
        $selectAll = filter_var($request->input('select_all_pages'), FILTER_VALIDATE_BOOLEAN);

        if ($selectAll) {
            $productIds = Product::query()->pluck('id')->all();
        } else {
            $productIds = $request->input('product_ids', []);
        }

        if (empty($productIds)) {
            return back()->with('error', 'No se ha seleccionado ningún producto para procesar.');
        }

        $count = count($productIds);

        if ($action === 'delete') {
            DB::transaction(function () use ($productIds): void {
                StockMovement::whereIn('product_id', $productIds)->delete();
                InventoryBalance::whereIn('product_id', $productIds)->delete();
                StockReservation::whereIn('product_id', $productIds)->delete();
                OrderItem::whereIn('product_id', $productIds)->delete();

                $images = ProductImage::whereIn('product_id', $productIds)->get();
                foreach ($images as $img) {
                    if ($img->file_path && ! str_starts_with($img->file_path, 'http')) {
                        Storage::disk('public')->delete($img->file_path);
                    }
                    $img->delete();
                }

                Product::whereIn('id', $productIds)->delete();
            });

            AuditLog::log('products_bulk_deleted', 'products', null, null, [
                'count' => $count,
                'action' => 'delete',
            ]);

            return redirect()->route('admin.products.index')
                ->with('status', "Se han eliminado definitivamente {$count} productos del catálogo.");
        }

        if ($action === 'archive') {
            Product::whereIn('id', $productIds)->update(['status' => 'archived']);

            AuditLog::log('products_bulk_archived', 'products', null, null, [
                'count' => $count,
                'action' => 'archive',
            ]);

            return redirect()->route('admin.products.index')
                ->with('status', "Se han archivado {$count} productos del catálogo.");
        }

        if ($action === 'publish') {
            Product::whereIn('id', $productIds)->update(['status' => 'published']);

            return redirect()->route('admin.products.index')
                ->with('status', "Se han publicado {$count} productos en el catálogo.");
        }

        return back();
    }

    /**
     * Muestra la página explicativa, instructivo y previsualización de la plantilla Excel/CSV.
     */
    public function templateGuide(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        $columns = [
            ['key' => 'sku', 'label' => 'SKU Único', 'required' => true, 'desc' => 'Código identificador único del producto. No debe repetirse.', 'example' => 'LEN-LEG-PRO5-4060'],
            ['key' => 'name', 'label' => 'Nombre Comercial', 'required' => true, 'desc' => 'Nombre descriptivo completo de la laptop.', 'example' => 'Lenovo Legion Pro 5 16IRX9 i7 32GB RTX 4060'],
            ['key' => 'price', 'label' => 'Precio Unitario', 'required' => true, 'desc' => 'Monto numérico positivo (usar punto decimal).', 'example' => '6499.00'],
            ['key' => 'currency', 'label' => 'Moneda', 'required' => true, 'desc' => 'Moneda permitida: PEN (Soles) o USD (Dólares).', 'example' => 'PEN'],
            ['key' => 'category', 'label' => 'Categoría', 'required' => true, 'desc' => 'Nombre exacto de una categoría activa registrada en el sistema.', 'example' => $categories->first()?->name ?? 'Laptops Gamer'],
            ['key' => 'brand', 'label' => 'Marca', 'required' => true, 'desc' => 'Nombre exacto de una marca activa registrada en el sistema.', 'example' => $brands->first()?->name ?? 'Lenovo'],
            ['key' => 'status', 'label' => 'Estado Inicial', 'required' => true, 'desc' => 'Estado en catálogo: draft (borrador), published (publicado) o archived.', 'example' => 'published'],
            ['key' => 'short_description', 'label' => 'Resumen Comercial', 'required' => false, 'desc' => 'Resumen breve en 1 o 2 líneas para WhatsApp y cotizaciones rápidas.', 'example' => 'Laptop gaming de 16" con pantalla 240Hz, Core i7 y gráfica RTX 4060.'],
            ['key' => 'description', 'label' => 'Descripción Detallada', 'required' => false, 'desc' => 'Presentación completa comercial, sistema de refrigeración y ventajas.', 'example' => 'Diseñada para jugadores exigentes y creadores de contenido...'],
            ['key' => 'barcode', 'label' => 'Código EAN / Barras', 'required' => false, 'desc' => 'Código de barras de 8 a 13 dígitos. Debe ser único si se ingresa.', 'example' => '7701234567890'],
            ['key' => 'procesador', 'label' => 'CPU / Procesador', 'required' => false, 'desc' => 'Modelo de CPU, núcleos y frecuencia.', 'example' => 'Intel Core i7-14700HX (20 núcleos, 5.5GHz)'],
            ['key' => 'ram', 'label' => 'Memoria RAM', 'required' => false, 'desc' => 'Capacidad, tecnología y velocidad.', 'example' => '32 GB DDR5 5600MHz'],
            ['key' => 'almacenamiento', 'label' => 'SSD / Almacenamiento', 'required' => false, 'desc' => 'Capacidad y tipo de unidad.', 'example' => '1 TB SSD M.2 PCIe 4.0 NVMe'],
            ['key' => 'pantalla', 'label' => 'Pantalla', 'required' => false, 'desc' => 'Pulgadas, resolución, tipo de panel y tasa de refresco.', 'example' => '16" WQXGA (2560x1600) IPS 240Hz 500 nits'],
            ['key' => 'grafica', 'label' => 'Tarjeta Gráfica (GPU)', 'required' => false, 'desc' => 'Chip gráfico y VRAM dedicada.', 'example' => 'NVIDIA GeForce RTX 4060 8GB GDDR6'],
            ['key' => 'sistema_operativo', 'label' => 'Sistema Operativo', 'required' => false, 'desc' => 'SO preinstalado o compatible.', 'example' => 'Windows 11 Home'],
            ['key' => 'conectividad', 'label' => 'Puertos y Red', 'required' => false, 'desc' => 'Wi-Fi, Bluetooth y puertos USB/HDMI/Thunderbolt.', 'example' => 'Wi-Fi 6E, Bluetooth 5.2, Thunderbolt 4, HDMI 2.1'],
            ['key' => 'garantia', 'label' => 'Garantía Oficial', 'required' => false, 'desc' => 'Plazo y cobertura en Perú.', 'example' => '12 meses de garantía oficial en Perú'],
            ['key' => 'image_url_1', 'label' => 'URL Imagen 1 (Principal)', 'required' => false, 'desc' => 'Enlace web directo HTTPS a la imagen frontal del equipo.', 'example' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800'],
            ['key' => 'image_url_2', 'label' => 'URL Imagen 2', 'required' => false, 'desc' => 'Enlace web directo HTTPS a foto lateral o teclado.', 'example' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800'],
            ['key' => 'image_url_3', 'label' => 'URL Imagen 3', 'required' => false, 'desc' => 'Enlace web directo HTTPS a foto adicional de puertos/caja.', 'example' => ''],
        ];

        $sampleRows = [
            [
                'sku' => 'LEN-LEG-PRO5-4060',
                'name' => 'Lenovo Legion Pro 5 16IRX9 Intel Core i7 32GB RTX 4060',
                'price' => '6499.00',
                'currency' => 'PEN',
                'category' => 'Laptops Gamer',
                'brand' => 'Lenovo',
                'status' => 'published',
                'short_description' => 'Portátil gamer de 16" 240Hz con Core i7 y gráfica RTX 4060 de alto TGP.',
                'description' => 'La Legion Pro 5 ofrece máxima potencia y refrigeración ColdFront 5.0 con chasis de aluminio aeroespacial.',
                'barcode' => '7701234567891',
                'procesador' => 'Intel Core i7-14700HX (20 núcleos, 5.5GHz)',
                'ram' => '32 GB DDR5 5600MHz',
                'almacenamiento' => '1 TB SSD M.2 PCIe 4.0 NVMe',
                'pantalla' => '16" WQXGA (2560x1600) IPS 240Hz 500 nits',
                'grafica' => 'NVIDIA GeForce RTX 4060 8GB GDDR6',
                'sistema_operativo' => 'Windows 11 Home',
                'conectividad' => 'Wi-Fi 6E, Bluetooth 5.2, Thunderbolt 4',
                'garantia' => '12 meses de garantía oficial en Perú',
                'image_url_1' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800',
                'image_url_2' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800',
                'image_url_3' => '',
            ],
            [
                'sku' => 'ASUS-ZEPH-G16-4070',
                'name' => 'ASUS ROG Zephyrus G16 OLED Intel Core Ultra 9 32GB RTX 4070',
                'price' => '8999.00',
                'currency' => 'PEN',
                'category' => 'Laptops Gamer',
                'brand' => 'ASUS',
                'status' => 'published',
                'short_description' => 'Ultradelgada premium con pantalla OLED 2.5K 240Hz y chasis mecanizado CNC.',
                'description' => 'Rendimiento supremo para creadores y jugadores con panel ROG Nebula OLED y altavoces envolventes.',
                'barcode' => '7701234567892',
                'procesador' => 'Intel Core Ultra 9 185H con NPU para IA',
                'ram' => '32 GB LPDDR5X 7467MHz',
                'almacenamiento' => '2 TB SSD M.2 PCIe 4.0 NVMe',
                'pantalla' => '16" 2.5K (2560x1600) OLED 240Hz 0.2ms 500 nits',
                'grafica' => 'NVIDIA GeForce RTX 4070 8GB GDDR6',
                'sistema_operativo' => 'Windows 11 Pro',
                'conectividad' => 'Wi-Fi 7, Thunderbolt 4, HDMI 2.1, Lector SD UHS-II',
                'garantia' => '24 meses de garantía internacional',
                'image_url_1' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800',
                'image_url_2' => '',
                'image_url_3' => '',
            ],
            [
                'sku' => 'DELL-XPS13-PLUS-9320',
                'name' => 'Dell XPS 13 Plus 9320 Intel Core i7 16GB SSD 512GB',
                'price' => '5499.00',
                'currency' => 'PEN',
                'category' => 'Laptops Ultraligeras y Estudiantes',
                'brand' => 'Dell',
                'status' => 'draft',
                'short_description' => 'Elegancia ejecutiva minimalista, touchpad de cristal sin fisuras y 1.2 kg.',
                'description' => 'El ultrabook definitivo para ejecutivos con pantalla InfinityEdge y acabado en aluminio aeroespacial.',
                'barcode' => '7701234567893',
                'procesador' => 'Intel Core i7-1360P (12 núcleos)',
                'ram' => '16 GB LPDDR5',
                'almacenamiento' => '512 GB SSD M.2 PCIe',
                'pantalla' => '13.4" FHD+ (1920x1200) InfinityEdge 500 nits',
                'grafica' => 'Intel Iris Xe Graphics',
                'sistema_operativo' => 'Windows 11 Pro',
                'conectividad' => '2x Thunderbolt 4, Wi-Fi 6E',
                'garantia' => '12 meses con soporte Dell ProSupport',
                'image_url_1' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800',
                'image_url_2' => '',
                'image_url_3' => '',
            ],
        ];

        return view('admin.products.template-guide', compact('categories', 'brands', 'columns', 'sampleRows'));
    }

    /**
     * Endpoint AJAX para generar o autocompletar datos del producto con Gemini AI.
     */
    public function geminiAutofill(Request $request, GeminiService $geminiService): JsonResponse
    {
        $request->validate([
            'prompt' => ['required', 'string', 'min:2', 'max:3000'],
            'api_key' => ['nullable', 'string', 'max:200'],
        ]);

        if ($request->filled('api_key')) {
            $geminiService->saveApiKey($request->api_key);
        }

        $result = $geminiService->generateProductData($request->prompt);

        return response()->json([
            'success' => true,
            'data' => $result,
            'has_key' => $geminiService->hasApiKey(),
        ]);
    }

    /**
     * Guarda la clave de Gemini AI enviada desde el panel.
     */
    public function saveGeminiKey(Request $request, GeminiService $geminiService): JsonResponse
    {
        $request->validate([
            'api_key' => ['required', 'string', 'max:200'],
        ]);

        $geminiService->saveApiKey($request->api_key);

        return response()->json([
            'success' => true,
            'message' => 'API Key de Gemini AI guardada correctamente.',
            'has_key' => true,
        ]);
    }

    /**
     * Descarga una plantilla CSV, compatible con Excel, para altas masivas.
     */
    public function downloadImportTemplate()
    {
        $headers = ['sku', 'name', 'price', 'currency', 'category', 'brand', 'status', 'short_description', 'description', 'barcode', 'procesador', 'ram', 'almacenamiento', 'pantalla', 'grafica', 'sistema_operativo', 'conectividad', 'garantia', 'image_url_1', 'image_url_2', 'image_url_3'];
        $example = ['EJEMPLO-001', 'Laptop de ejemplo', '3499.00', 'PEN', 'Ultrabooks', 'Lenovo', 'draft', 'Resumen comercial', 'Descripción comercial completa', '7701234567890', 'Intel Core i7', '16 GB DDR5', '512 GB SSD', '14 pulgadas IPS', 'Intel Iris Xe', 'Windows 11', 'Wi-Fi 6, USB-C', '12 meses', 'https://ejemplo.com/frontal.jpg', '', ''];

        return response()->streamDownload(function () use ($headers, $example): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers);
            fputcsv($output, $example);
            fclose($output);
        }, 'plantilla-productos.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Importa productos desde el CSV descargable, resolviendo categoría y marca por nombre.
     */
    public function import(ImportProductsRequest $request): RedirectResponse
    {
        $file = fopen($request->file('products_file')->getRealPath(), 'r');
        $headers = fgetcsv($file);

        if ($headers === false) {
            throw ValidationException::withMessages(['products_file' => 'El archivo CSV está vacío.']);
        }

        $headers = array_map(fn (string $header): string => Str::of($header)->trim()->replace("\xEF\xBB\xBF", '')->lower()->toString(), $headers);
        $requiredHeaders = ['sku', 'name', 'price', 'currency', 'category', 'brand', 'status'];

        if (array_diff($requiredHeaders, $headers) !== []) {
            throw ValidationException::withMessages(['products_file' => 'La plantilla debe conservar las columnas obligatorias. Descargue una nueva plantilla e inténtelo nuevamente.']);
        }

        $categories = Category::where('is_active', true)->get()->keyBy(fn (Category $category): string => Str::lower($category->name));
        $brands = Brand::where('is_active', true)->get()->keyBy(fn (Brand $brand): string => Str::lower($brand->name));
        $rows = [];
        $rowNumber = 1;

        while (($values = fgetcsv($file)) !== false) {
            $rowNumber++;
            $values = array_pad($values, count($headers), '');
            $row = array_combine($headers, array_slice($values, 0, count($headers)));

            if (collect($row)->filter(fn ($value): bool => filled($value))->isEmpty()) {
                continue;
            }

            $sku = trim((string) ($row['sku'] ?? ''));
            $catName = trim((string) ($row['category'] ?? ''));
            $category = $categories->get(Str::lower($catName));

            // Si la categoría no existe en la BD, la creamos automáticamente para no trabar la importación
            if (! $category && $catName !== '') {
                $createdCat = Category::firstOrCreate(
                    ['slug' => Str::slug($catName)],
                    ['name' => $catName, 'is_active' => true]
                );
                $categories->put(Str::lower($catName), $createdCat);
                $category = $createdCat;
            }

            $brandName = trim((string) ($row['brand'] ?? ''));
            $brand = $brands->get(Str::lower($brandName));

            // Si la marca no existe en la BD, la creamos automáticamente
            if (! $brand && $brandName !== '') {
                $createdBrand = Brand::firstOrCreate(
                    ['slug' => Str::slug($brandName)],
                    ['name' => $brandName, 'is_active' => true]
                );
                $brands->put(Str::lower($brandName), $createdBrand);
                $brand = $createdBrand;
            }

            // Normalizar moneda (por defecto PEN)
            $currency = strtoupper(trim((string) ($row['currency'] ?? 'PEN')));
            if (! in_array($currency, ['PEN', 'USD'], true)) {
                $currency = 'PEN';
            }

            // Normalizar estado (por defecto published)
            $rawStatus = Str::lower(trim((string) ($row['status'] ?? 'published')));
            $status = match ($rawStatus) {
                'publicado', 'published', 'activo', 'active' => 'published',
                'borrador', 'draft', 'inactivo' => 'draft',
                'archivado', 'archived' => 'archived',
                default => 'published',
            };

            // Limpiar precio (quitar signos S/, $, comas o espacios)
            $rawPrice = preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string) ($row['price'] ?? '0')));
            $price = is_numeric($rawPrice) ? (float) $rawPrice : null;

            if ($sku === '' || blank($row['name'] ?? null) || $price === null || $price <= 0 || ! $category || ! $brand) {
                $missingFields = [];
                if ($sku === '') $missingFields[] = 'SKU vacío';
                if (blank($row['name'] ?? null)) $missingFields[] = 'Nombre vacío';
                if ($price === null || $price <= 0) $missingFields[] = 'Precio inválido ('.($row['price'] ?? 'vacío').')';
                if (! $category) $missingFields[] = 'Categoría no especificada';
                if (! $brand) $missingFields[] = 'Marca no especificada';

                $detail = implode(', ', $missingFields);
                throw ValidationException::withMessages(['products_file' => "La fila {$rowNumber} tiene datos inválidos: {$detail}."]);
            }

            if (isset($rows[$sku]) || Product::where('sku', $sku)->exists()) {
                throw ValidationException::withMessages(['products_file' => "El SKU {$sku} de la fila {$rowNumber} ya existe o está repetido en el archivo."]);
            }

            $barcode = trim((string) ($row['barcode'] ?? ''));
            if ($barcode !== '' && (Product::where('barcode', $barcode)->exists() || collect($rows)->contains('barcode', $barcode))) {
                throw ValidationException::withMessages(['products_file' => "El EAN {$barcode} de la fila {$rowNumber} ya pertenece a otro producto."]);
            }

            $rows[$sku] = [
                'row' => $row,
                'category' => $category,
                'brand' => $brand,
                'barcode' => $barcode,
                'price' => $price,
                'currency' => $currency,
                'status' => $status,
            ];
        }

        fclose($file);

        if ($rows === []) {
            throw ValidationException::withMessages(['products_file' => 'No se encontraron productos para importar.']);
        }

        DB::transaction(function () use ($rows): void {
            foreach ($rows as $entry) {
                $row = $entry['row'];
                $technicalSpecs = collect(['procesador', 'ram', 'almacenamiento', 'pantalla', 'grafica', 'sistema_operativo', 'conectividad', 'garantia'])
                    ->mapWithKeys(fn (string $field): array => [$field => trim((string) ($row[$field] ?? ''))])
                    ->filter()
                    ->all();

                $product = Product::create([
                    'sku' => trim($row['sku']),
                    'name' => trim($row['name']),
                    'price' => $entry['price'],
                    'currency' => $entry['currency'],
                    'category_id' => $entry['category']->id,
                    'brand_id' => $entry['brand']->id,
                    'status' => $entry['status'],
                    'short_description' => ($row['short_description'] ?? null) ?: null,
                    'description' => ($row['description'] ?? null) ?: null,
                    'barcode' => $entry['barcode'] ?: null,
                    'technical_specs' => $technicalSpecs,
                ]);

                $this->storeImageUrls($product, [
                    $row['image_url_1'] ?? null,
                    $row['image_url_2'] ?? null,
                    $row['image_url_3'] ?? null,
                ]);

                AuditLog::log('product_imported', 'products', $product->id, null, ['sku' => $product->sku]);
            }
        });

        return redirect()->route('admin.products.index')->with('status', count($rows).' productos importados correctamente.');
    }

    private function storeProductImages(Request $request, Product $product): void
    {
        $currentCount = $product->images()->count();

        // 1. Guardar archivos locales subidos
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }
                if ($currentCount >= 15) {
                    break;
                }

                $path = $file->store("products/{$product->id}", 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'file_path' => $path,
                    'is_primary' => $currentCount === 0,
                    'sort_order' => $currentCount,
                    'alt_text' => $product->name,
                ]);
                $currentCount++;
            }
        }

        // 2. Guardar URLs agregadas
        $urls = $request->input('image_urls', []);
        if (is_array($urls)) {
            $this->storeImageUrls($product, $urls, $currentCount);
        }
    }

    private function storeImageUrls(Product $product, array $urls, ?int $currentCount = null): void
    {
        $currentCount ??= $product->images()->count();

        foreach (array_filter($urls) as $url) {
            $cleanUrl = trim((string) $url);
            if (empty($cleanUrl)) {
                continue;
            }
            if ($currentCount >= 15) {
                break;
            }

            // Evitar duplicar la misma URL para este producto
            if ($product->images()->where('file_path', $cleanUrl)->exists()) {
                continue;
            }

            ProductImage::create([
                'product_id' => $product->id,
                'file_path' => $cleanUrl,
                'is_primary' => $currentCount === 0,
                'sort_order' => $currentCount,
                'alt_text' => $product->name,
            ]);
            $currentCount++;
        }
    }
}
