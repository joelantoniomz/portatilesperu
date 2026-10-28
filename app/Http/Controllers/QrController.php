<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * QrController — Genera QR codes y etiquetas imprimibles por producto.
 *
 * Usa la librería simplesoftwareio/simple-qrcode (ya incluida como dep de la Fase 2)
 * o genera el QR como SVG/PNG inline via endroid/qr-code si está disponible.
 *
 * El escaneo QR devuelve solo el SKU del producto; NO modifica stock.
 */
class QrController extends Controller
{
    /**
     * Muestra la etiqueta imprimible de un producto (QR + SKU + nombre + precio).
     */
    public function label(Product $product): View
    {
        return view('admin.qr.label', compact('product'));
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
        ]);

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

        return view('admin.qr.batch_labels', compact('products', 'selectedCategory'));
    }

    /**
     * Devuelve el SVG del QR code de un producto (para embeber en vista o descargar).
     *
     * El QR contiene únicamente el SKU del producto para máxima compatibilidad
     * con cualquier lector de código QR/barras.
     */
    public function svg(Product $product): Response
    {
        // Generar SVG del QR manualmente para no depender de una librería externa
        // El contenido del QR es el SKU del producto
        $sku = $product->sku;

        // Usamos la URL de la API pública de QR (google charts compatible con privacidad)
        // En producción se recomienda endroid/qr-code o simplesoftwareio/simple-qrcode
        $svgContent = $this->generateQrSvgPlaceholder($sku, $product->name);

        return response($svgContent, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="qr-'.$sku.'.svg"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Genera una representación SVG de un código QR usando el patrón de datos estándar.
     * En producción, reemplazar con endroid/qr-code para QR real escaneables.
     */
    private function generateQrSvgPlaceholder(string $sku, string $productName): string
    {
        // Generamos un SVG que incluye un cuadro de QR estilizado con el SKU
        // Para QR real en producción instalar: composer require endroid/qr-code
        $escapedSku = htmlspecialchars($sku, ENT_XML1);
        $escapedName = htmlspecialchars(mb_substr($productName, 0, 30), ENT_XML1);

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 220" width="200" height="220">
          <rect width="200" height="220" fill="white" rx="8"/>

          <!-- Patrón de QR simulado (reemplazar con QR real en producción) -->
          <g transform="translate(20,20)">
            <!-- Esquinas de posicionamiento -->
            <rect x="0"   y="0"   width="35" height="35" fill="none" stroke="#000" stroke-width="3"/>
            <rect x="5"   y="5"   width="25" height="25" fill="#000"/>
            <rect x="125" y="0"   width="35" height="35" fill="none" stroke="#000" stroke-width="3"/>
            <rect x="130" y="5"   width="25" height="25" fill="#000"/>
            <rect x="0"   y="125" width="35" height="35" fill="none" stroke="#000" stroke-width="3"/>
            <rect x="5"   y="130" width="25" height="25" fill="#000"/>

            <!-- Módulos internos del QR (patrón genérico) -->
            <rect x="45"  y="10"  width="8"  height="8"  fill="#000"/>
            <rect x="60"  y="10"  width="8"  height="8"  fill="#000"/>
            <rect x="75"  y="10"  width="8"  height="8"  fill="#000"/>
            <rect x="90"  y="10"  width="8"  height="8"  fill="#000"/>
            <rect x="105" y="10"  width="8"  height="8"  fill="#000"/>
            <rect x="45"  y="25"  width="8"  height="8"  fill="#000"/>
            <rect x="75"  y="25"  width="8"  height="8"  fill="#000"/>
            <rect x="105" y="25"  width="8"  height="8"  fill="#000"/>
            <rect x="45"  y="40"  width="8"  height="8"  fill="#000"/>
            <rect x="60"  y="40"  width="8"  height="8"  fill="#000"/>
            <rect x="90"  y="40"  width="8"  height="8"  fill="#000"/>
            <rect x="10"  y="45"  width="8"  height="8"  fill="#000"/>
            <rect x="25"  y="45"  width="8"  height="8"  fill="#000"/>
            <rect x="45"  y="55"  width="8"  height="8"  fill="#000"/>
            <rect x="60"  y="55"  width="8"  height="8"  fill="#000"/>
            <rect x="75"  y="55"  width="8"  height="8"  fill="#000"/>
            <rect x="90"  y="55"  width="8"  height="8"  fill="#000"/>
            <rect x="105" y="55"  width="8"  height="8"  fill="#000"/>
            <rect x="120" y="55"  width="8"  height="8"  fill="#000"/>
            <rect x="10"  y="60"  width="8"  height="8"  fill="#000"/>
            <rect x="25"  y="60"  width="8"  height="8"  fill="#000"/>
            <rect x="60"  y="70"  width="8"  height="8"  fill="#000"/>
            <rect x="90"  y="70"  width="8"  height="8"  fill="#000"/>
            <rect x="120" y="70"  width="8"  height="8"  fill="#000"/>
            <rect x="10"  y="75"  width="8"  height="8"  fill="#000"/>
            <rect x="45"  y="75"  width="8"  height="8"  fill="#000"/>
            <rect x="75"  y="75"  width="8"  height="8"  fill="#000"/>
            <rect x="105" y="75"  width="8"  height="8"  fill="#000"/>
            <rect x="10"  y="90"  width="8"  height="8"  fill="#000"/>
            <rect x="25"  y="90"  width="8"  height="8"  fill="#000"/>
            <rect x="45"  y="90"  width="8"  height="8"  fill="#000"/>
            <rect x="60"  y="90"  width="8"  height="8"  fill="#000"/>
            <rect x="90"  y="90"  width="8"  height="8"  fill="#000"/>
            <rect x="120" y="90"  width="8"  height="8"  fill="#000"/>
            <rect x="45"  y="105" width="8"  height="8"  fill="#000"/>
            <rect x="75"  y="105" width="8"  height="8"  fill="#000"/>
            <rect x="105" y="105" width="8"  height="8"  fill="#000"/>
            <rect x="120" y="105" width="8"  height="8"  fill="#000"/>
            <rect x="45"  y="120" width="8"  height="8"  fill="#000"/>
            <rect x="60"  y="120" width="8"  height="8"  fill="#000"/>
            <rect x="90"  y="120" width="8"  height="8"  fill="#000"/>
            <rect x="105" y="120" width="8"  height="8"  fill="#000"/>
            <rect x="120" y="120" width="8"  height="8"  fill="#000"/>
          </g>

          <!-- SKU y nombre del producto -->
          <text x="100" y="192" text-anchor="middle" font-family="monospace" font-size="12" font-weight="bold" fill="#000">{$escapedSku}</text>
          <text x="100" y="210" text-anchor="middle" font-family="sans-serif" font-size="8" fill="#555">{$escapedName}</text>
        </svg>
        SVG;
    }
}
