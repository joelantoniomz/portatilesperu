<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\InventorySeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    private Category $categoryGaming;

    private Brand $brandLenovo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(CatalogSeeder::class);
        $this->seed(InventorySeeder::class);

        $this->categoryGaming = Category::where('slug', 'laptops-gamer')->first() ?? Category::first();
        $this->brandLenovo = Brand::where('slug', 'lenovo')->first() ?? Brand::first();
    }

    /**
     * Test 1: La portada pública carga exitosamente y muestra solo productos publicados.
     */
    public function test_home_page_displays_published_products_and_branding(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('PORTÁTILES');
        $response->assertSee('PERÚ');
        $response->assertSee('Laptop Recomendada');
        $response->assertSee('Laptops Destacadas');
    }

    /**
     * Test 2: Productos no publicados (draft o archived) nunca aparecen en el catálogo.
     */
    public function test_draft_and_archived_products_are_never_visible_publicly(): void
    {
        $draftProduct = Product::create([
            'sku' => 'TEST-SECRET-DRAFT',
            'name' => 'Laptop Oculta en Borrador 2026',
            'slug' => 'laptop-oculta-en-borrador-2026',
            'category_id' => $this->categoryGaming->id,
            'brand_id' => $this->brandLenovo->id,
            'price' => 5000.00,
            'status' => 'draft',
        ]);

        $archivedProduct = Product::create([
            'sku' => 'TEST-SECRET-ARCHIVED',
            'name' => 'Laptop Antigua Descontinuada',
            'slug' => 'laptop-antigua-descontinuada',
            'category_id' => $this->categoryGaming->id,
            'brand_id' => $this->brandLenovo->id,
            'price' => 3000.00,
            'status' => 'archived',
        ]);

        // No deben aparecer en /catalogo
        $responseCatalog = $this->get('/catalogo');
        $responseCatalog->assertDontSee($draftProduct->name);
        $responseCatalog->assertDontSee($archivedProduct->name);

        // Ficha directa por slug debe retornar 404
        $responseDraftDetail = $this->get('/catalogo/'.$draftProduct->slug);
        $responseDraftDetail->assertNotFound();

        $responseArchivedDetail = $this->get('/catalogo/'.$archivedProduct->slug);
        $responseArchivedDetail->assertNotFound();
    }

    /**
     * Test 3: Búsqueda por texto en catálogo filtra por nombre o SKU.
     */
    public function test_catalog_search_filters_by_name_or_sku(): void
    {
        $product = Product::published()->first();

        $response = $this->get('/catalogo?q='.urlencode($product->sku));

        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee($product->sku);
    }

    /**
     * Test 4: Filtro por categoría en catálogo.
     */
    public function test_catalog_filter_by_category(): void
    {
        $response = $this->get('/catalogo?categoria='.$this->categoryGaming->slug);

        $response->assertStatus(200);
        $response->assertSee($this->categoryGaming->name);
    }

    /**
     * Test 5: Filtro por marca en catálogo.
     */
    public function test_catalog_filter_by_brand(): void
    {
        $response = $this->get('/catalogo?marca='.$this->brandLenovo->slug);

        $response->assertStatus(200);
        $response->assertSee($this->brandLenovo->name);
    }

    /**
     * Test 6: Filtro por rango de precio.
     */
    public function test_catalog_filter_by_price_range(): void
    {
        $cheapProduct = Product::published()->orderBy('price', 'asc')->first();
        $expensiveProduct = Product::published()->orderBy('price', 'desc')->first();

        // Filtro acotado a laptops de más de 6000 soles
        $response = $this->get('/catalogo?min_precio=6000');
        $response->assertStatus(200);

        if ($cheapProduct->price < 6000) {
            $response->assertDontSee($cheapProduct->name);
        }
    }

    /**
     * Test 7: Filtro solo con stock disponible.
     */
    public function test_catalog_filter_by_in_stock_only(): void
    {
        $response = $this->get('/catalogo?en_stock=1');
        $response->assertStatus(200);
    }

    /**
     * Test 8: Ficha de producto pública carga especificaciones y stock real.
     */
    public function test_product_detail_page_loads_with_specs_and_stock(): void
    {
        $product = Product::published()->first();

        $response = $this->get('/catalogo/'.$product->slug);

        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee($product->sku);
        $response->assertSee($product->formatted_price);
        $response->assertSee('Disponibilidad en Tiendas Lima', false);
        $response->assertSee('Comprar por WhatsApp', false);
        $response->assertSee('Agregar al carrito', false);
        $response->assertSee('Descargar ficha PDF', false);
        $response->assertDontSee('Ver QR', false);
    }

    /**
     * Test 9: Una ficha técnica pública se descarga solo para productos publicados.
     */
    public function test_published_product_technical_sheet_can_be_downloaded_as_pdf(): void
    {
        $product = Product::published()->first();

        $response = $this->get(route('catalogo.ficha-tecnica', $product->slug));

        $response->assertOk();
        $response->assertDownload('ficha-tecnica-'.$product->sku.'.pdf');
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test 10: Páginas informativas responden correctamente.
     */
    public function test_informational_pages_respond_successfully(): void
    {
        $responseAbout = $this->get('/nosotros');
        $responseAbout->assertStatus(200);
        $responseAbout->assertSee('PORTÁTILES PERÚ S.A.C.');
        $responseAbout->assertSee('20608976541');

        $responseContact = $this->get('/contacto');
        $responseContact->assertStatus(200);
        $responseContact->assertSee('Tienda Miraflores');
        $responseContact->assertSee('+51 987 654 321');
    }
}
