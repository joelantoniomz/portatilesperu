<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CatalogPdfController extends Controller
{
    private const TEMPLATES = ['minimalista', 'corporativo', 'premium', 'comercial', 'tecnico', 'editorial', 'mayorista', 'moderno'];

    public function index(): View
    {
        return view('admin.catalog-pdf.index', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::published()->orderBy('name')->get(['id', 'name', 'sku']),
            'templates' => self::TEMPLATES,
            'settings' => Setting::query()->pluck('value', 'key'),
        ]);
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'template' => 'required|in:'.implode(',', self::TEMPLATES),
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
            'brand_ids' => 'nullable|array',
            'brand_ids.*' => 'integer|exists:brands,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
            'paper' => 'required|in:a4,letter',
            'orientation' => 'required|in:portrait,landscape',
            'format' => 'nullable|in:pdf,csv',
            'columns' => 'nullable|integer|in:3,4',
            'company_name' => 'nullable|string|max:120',
            'company_phone' => 'nullable|string|max:40',
            'company_email' => 'nullable|email|max:255',
            'payment_methods' => 'nullable|string|max:500',
            'cover_title' => 'nullable|string|max:160',
            'show_prices' => 'boolean', 'show_sku' => 'boolean', 'show_stock' => 'boolean', 'show_specs' => 'boolean',
        ]);

        $query = Product::published()->with(['brand', 'category', 'images', 'inventoryBalances'])->orderBy('name');
        if (! empty($data['product_ids'])) {
            $query->whereIn('id', $data['product_ids']);
        }
        if (! empty($data['category_ids'])) {
            $query->whereIn('category_id', $data['category_ids']);
        }
        if (! empty($data['brand_ids'])) {
            $query->whereIn('brand_id', $data['brand_ids']);
        }
        $products = $query->get();
        abort_if($products->isEmpty(), 422, 'No hay productos publicados para los filtros seleccionados.');

        $options = collect(['show_prices', 'show_sku', 'show_stock', 'show_specs'])->mapWithKeys(fn ($key) => [$key => $request->boolean($key)])->all();
        $options += $request->only(['columns', 'company_name', 'company_phone', 'company_email', 'payment_methods', 'cover_title']);
        $options['columns'] = (int) ($options['columns'] ?? 4);
        $options['company_name'] = $options['company_name'] ?? Setting::getValue('company_name', 'Portátiles Perú');
        $options['company_phone'] = $options['company_phone'] ?? Setting::getValue('whatsapp_number', '');
        $options['company_email'] = $options['company_email'] ?? Setting::getValue('company_email', '');
        $options['payment_methods'] = $options['payment_methods'] ?? Setting::getValue('payment_methods', 'Yape · Plin · Visa · Mastercard');
        $options['cover_title'] = $options['cover_title'] ?? 'Catálogo de productos';
        foreach (['company_name', 'company_phone', 'company_email', 'payment_methods'] as $settingKey) {
            if (array_key_exists($settingKey, $options) && $options[$settingKey] !== null) {
                Setting::query()->updateOrCreate(['key' => $settingKey], ['value' => $options[$settingKey]]);
            }
        }
        $options['category_names'] = $products->pluck('category.name')->filter()->unique()->values()->implode(' · ');
        AuditLog::log('catalog_pdf.generated', Product::class, null, null, ['template' => $data['template'], 'products' => $products->count()]);
        $timestamp = now()->format('Ymd-His');

        if (($data['format'] ?? 'pdf') === 'csv') {
            return $this->downloadSpreadsheet($products, $timestamp);
        }

        $pdf = Pdf::loadView('pdf.templates.'.$data['template'], compact('products', 'options'))
            ->setPaper($data['paper'], $data['orientation'])
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('chroot', [base_path(), public_path(), storage_path()]);

        $name = 'catalogo-'.$data['template'].'-'.$timestamp.'.pdf';

        return $request->boolean('download') ? $pdf->download($name) : $pdf->stream($name);
    }

    /**
     * Página de previsualización interactiva del catálogo con diseño visual y botón lateral de descarga.
     */
    public function previewPage(Request $request): View
    {
        $data = $request->validate([
            'template' => 'nullable|in:'.implode(',', self::TEMPLATES),
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
            'brand_ids' => 'nullable|array',
            'brand_ids.*' => 'integer|exists:brands,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
            'paper' => 'nullable|in:a4,letter',
            'orientation' => 'nullable|in:portrait,landscape',
            'columns' => 'nullable|integer|in:3,4',
            'company_name' => 'nullable|string|max:120',
            'company_phone' => 'nullable|string|max:40',
            'company_email' => 'nullable|email|max:255',
            'payment_methods' => 'nullable|string|max:500',
            'cover_title' => 'nullable|string|max:160',
            'show_prices' => 'boolean',
            'show_sku' => 'boolean',
            'show_stock' => 'boolean',
            'show_specs' => 'boolean',
        ]);

        $query = Product::published()->with(['brand', 'category', 'images', 'inventoryBalances'])->orderBy('name');
        if (! empty($data['product_ids'])) {
            $query->whereIn('id', $data['product_ids']);
        }
        if (! empty($data['category_ids'])) {
            $query->whereIn('category_id', $data['category_ids']);
        }
        if (! empty($data['brand_ids'])) {
            $query->whereIn('brand_id', $data['brand_ids']);
        }
        $products = $query->get();

        $options = collect(['show_prices', 'show_sku', 'show_stock', 'show_specs'])->mapWithKeys(fn ($key) => [$key => $request->boolean($key, true)])->all();
        $options += $request->only(['columns', 'company_name', 'company_phone', 'company_email', 'payment_methods', 'cover_title']);
        $options['template'] = $data['template'] ?? 'minimalista';
        $options['paper'] = $data['paper'] ?? 'a4';
        $options['orientation'] = $data['orientation'] ?? 'portrait';
        $options['columns'] = (int) ($options['columns'] ?? 4);
        $options['company_name'] = $options['company_name'] ?? Setting::getValue('company_name', 'Portátiles Perú');
        $options['company_phone'] = $options['company_phone'] ?? Setting::getValue('whatsapp_number', '');
        $options['company_email'] = $options['company_email'] ?? Setting::getValue('company_email', '');
        $options['payment_methods'] = $options['payment_methods'] ?? Setting::getValue('payment_methods', 'Yape · Plin · Visa · Mastercard');
        $options['cover_title'] = $options['cover_title'] ?? 'Catálogo de productos';
        $options['category_names'] = $products->pluck('category.name')->filter()->unique()->values()->implode(' · ');

        return view('admin.catalog-pdf.preview', compact('products', 'options', 'data'));
    }

    private function downloadSpreadsheet($products, string $timestamp): StreamedResponse
    {
        return response()->streamDownload(function () use ($products): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['SKU', 'Producto', 'Categoría', 'Marca', 'Precio', 'Moneda', 'Stock', 'Descripción', 'Ficha técnica']);

            foreach ($products as $product) {
                fputcsv($output, [
                    $product->sku,
                    $product->name,
                    $product->category?->name,
                    $product->brand?->name,
                    $product->price,
                    $product->currency,
                    $product->total_available_stock,
                    $product->short_description,
                    collect($product->technical_specs ?? [])->map(fn ($value, $key): string => ucfirst(str_replace('_', ' ', $key)).': '.$value)->implode(' | '),
                ]);
            }

            fclose($output);
        }, 'catalogo-'.$timestamp.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
