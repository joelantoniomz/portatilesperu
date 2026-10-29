<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * QrController — Genera códigos QR reales y etiquetas imprimibles por producto.
 *
 * Utiliza BaconQrCode para producir matrices QR estándar (ISO/IEC 18004),
 * 100% escaneables desde teléfonos móviles (cámara nativa, Google Lens, iOS, Android)
 * y pistolas lectoras USB / Bluetooth.
 */
class QrController extends Controller
{
    /**
     * Muestra la etiqueta imprimible de un producto (QR + SKU + nombre + precio).
     */
    public function label(Request $request, Product $product): View
    {
        $qrMode = $request->query('mode', 'url');

        return view('admin.qr.label', compact('product', 'qrMode'));
    }

    /**
     * Genera e imprime etiquetas en lote para múltiples productos o por categoría.
     */
    public function batchLabels(Request $request): View
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'product_ids' => 'nullable|array|max:200',
            'product_ids.*' => 'integer|exists:products,id',
            'mode' => 'nullable|in:url,sku',
        ]);

        $qrMode = $request->input('mode', 'url');

        $query = Product::query()->with(['brand:id,name', 'category:id,name']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
            $selectedCategory = Category::find($request->category_id);
        } else {
            $selectedCategory = null;
        }

        if ($request->filled('product_ids')) {
            $query->whereIn('id', $request->product_ids);
        }

        $products = $query->where('status', '!=', 'archived')->orderBy('name')->get();

        return view('admin.qr.batch_labels', compact('products', 'selectedCategory', 'qrMode'));
    }

    /**
     * Devuelve el SVG del código QR real de un producto.
     *
     * - Modo 'url' (por defecto): Codifica la URL pública del producto en el catálogo web
     *   para que al escanear desde cualquier celular se abra automáticamente la ficha,
     *   fotos, precio y stock en tiempo real.
     * - Modo 'sku': Codifica únicamente el SKU (para pistolas lectoras de inventario).
     */
    public function svg(Request $request, Product $product): Response
    {
        $mode = $request->query('mode', 'url');

        if ($mode === 'sku') {
            $qrContent = $product->sku;
        } else {
            $slug = $product->slug ?: Str::slug($product->name.'-'.$product->sku);
            $qrContent = route('catalogo.detalle', $slug);
        }

        $svgContent = $this->generateRealQrSvg($qrContent);

        return response($svgContent, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="qr-'.$product->sku.'.svg"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Genera un SVG de código QR estándar ISO escaneable con BaconQrCode.
     */
    private function generateRealQrSvg(string $content): string
    {
        try {
            $renderer = new ImageRenderer(
                new RendererStyle(240, 1),
                new SvgImageBackEnd
            );
            $writer = new Writer($renderer);

            return $writer->writeString($content);
        } catch (\Throwable $e) {
            // Fallback en caso de contingencia
            return $this->generateFallbackQrSvg($content);
        }
    }

    /**
     * Fallback SVG estructurado en caso de fallo inesperado de la librería.
     */
    private function generateFallbackQrSvg(string $content): string
    {
        $escaped = htmlspecialchars($content, ENT_XML1);

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200">
            <rect width="200" height="200" fill="#ffffff" />
            <text x="100" y="100" font-size="10" text-anchor="middle" fill="#333">{$escaped}</text>
        </svg>
        SVG;
    }
}
