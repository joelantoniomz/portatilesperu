<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->adminUser = User::where('email', 'admin@portatilesperu.com')->first();
        $this->product = Product::first();
    }

    /**
     * Verifica que el endpoint SVG retorne un SVG de código QR real válido.
     */
    public function test_qr_svg_returns_valid_xml_svg_with_real_qr_content(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.qr.svg', $this->product));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $response->getContent());
        $this->assertStringContainsString('</svg>', $response->getContent());
        // Debe contener elementos de dibujo reales generados por BaconQrCode
        $this->assertStringContainsString('<path', $response->getContent());
    }

    /**
     * Verifica que el endpoint SVG en modo SKU funcione correctamente.
     */
    public function test_qr_svg_with_sku_mode(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.qr.svg', ['product' => $this->product, 'mode' => 'sku']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $response->getContent());
    }

    /**
     * Verifica la vista de impresión de etiqueta individual.
     */
    public function test_qr_label_view_renders_correctly(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.qr.label', $this->product));

        $response->assertOk();
        $response->assertSee($this->product->sku);
        $response->assertSee('PORTÁTILES PERÚ');
    }

    /**
     * Verifica la vista de etiquetas en lote.
     */
    public function test_qr_batch_labels_view_renders_correctly(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.qr.batch'));

        $response->assertOk();
        $response->assertSee('Imprimir Lote Completo');
        $response->assertSee($this->product->sku);
    }

    /**
     * Verifica que el escáner de inventario pueda resolver un producto escaneando su URL de catálogo.
     */
    public function test_inventory_scan_lookup_resolves_product_from_catalog_url(): void
    {
        $catalogUrl = route('catalogo.detalle', $this->product->slug);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/admin/inventario/buscar-codigo?code='.urlencode($catalogUrl));

        $response->assertOk();
        $response->assertJson([
            'found' => true,
            'product' => [
                'id' => $this->product->id,
                'sku' => $this->product->sku,
            ],
        ]);
    }

    /**
     * Verifica que el escáner de inventario resuelva por URL con query ?sku=.
     */
    public function test_inventory_scan_lookup_resolves_product_from_url_with_sku_param(): void
    {
        $urlWithSku = route('catalogo.detalle', $this->product->slug).'?sku='.$this->product->sku;

        $response = $this->actingAs($this->adminUser)
            ->getJson('/admin/inventario/buscar-codigo?code='.urlencode($urlWithSku));

        $response->assertOk();
        $response->assertJson([
            'found' => true,
            'product' => [
                'sku' => $this->product->sku,
            ],
        ]);
    }
}
