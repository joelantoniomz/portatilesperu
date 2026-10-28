<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(CatalogSeeder::class);
    }

    public function test_editor_or_admin_can_view_products_list(): void
    {
        $editor = User::where('email', 'editor@portatilesperu.com')->first();

        $response = $this->actingAs($editor)->get('/admin/productos');

        $response->assertStatus(200);
        $response->assertSee('Catálogo de Laptops');
        $response->assertSee('LEN-LEG-PRO5-4060');
    }

    public function test_editor_can_create_product_with_specs_and_images(): void
    {
        Storage::fake('public');
        $editor = User::where('email', 'editor@portatilesperu.com')->first();
        $category = Category::first();
        $brand = Brand::first();

        $data = [
            'sku' => 'TEST-LAPTOP-001',
            'name' => 'Laptop de Prueba Antigravity 16"',
            'price' => 4599.00,
            'currency' => 'PEN',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'status' => 'published',
            'is_featured' => 1,
            'is_new' => 1,
            'min_stock_alert' => 4,
            'short_description' => 'Laptop de alta gama para pruebas unitarias.',
            'technical_specs' => [
                'procesador' => 'AMD Ryzen 9 7945HX',
                'ram' => '32 GB DDR5',
                'almacenamiento' => '1 TB NVMe',
            ],
            'images' => [
                UploadedFile::fake()->image('laptop_front.jpg', 800, 600),
                UploadedFile::fake()->image('laptop_side.png', 800, 600),
            ],
            'image_urls' => [
                'https://images.example.com/laptop-back.jpg',
                'https://images.example.com/laptop-keyboard.jpg',
            ],
        ];

        $response = $this->actingAs($editor)->post('/admin/productos', $data);

        $response->assertRedirect();

        $this->assertDatabaseHas('products', [
            'sku' => 'TEST-LAPTOP-001',
            'name' => 'Laptop de Prueba Antigravity 16"',
            'status' => 'published',
        ]);

        $createdProduct = Product::where('sku', 'TEST-LAPTOP-001')->first();
        $this->assertNotNull($createdProduct);
        $this->assertCount(4, $createdProduct->images);
        $this->assertEquals('AMD Ryzen 9 7945HX', $createdProduct->technical_specs['procesador']);
        $this->assertSame('https://images.example.com/laptop-back.jpg', $createdProduct->images->get(2)->url);

        // Verificar que la acción se registró en auditoría
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'product_created',
            'entity_id' => $createdProduct->id,
        ]);
    }

    public function test_duplicate_sku_is_rejected_by_server_validation(): void
    {
        $editor = User::where('email', 'editor@portatilesperu.com')->first();
        $category = Category::first();
        $brand = Brand::first();

        // Intentar registrar un producto con un SKU ya existente en CatalogSeeder
        $data = [
            'sku' => 'LEN-LEG-PRO5-4060', // Ya existe
            'name' => 'Laptop Duplicada',
            'price' => 3000.00,
            'currency' => 'PEN',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'status' => 'published',
        ];

        $response = $this->actingAs($editor)->post('/admin/productos', $data);

        $response->assertSessionHasErrors('sku');
    }

    public function test_negative_price_and_missing_required_fields_are_rejected(): void
    {
        $editor = User::where('email', 'editor@portatilesperu.com')->first();

        $data = [
            'sku' => '',
            'name' => '',
            'price' => -500.00, // Precio negativo inválido
            'currency' => 'PEN',
            'category_id' => 99999, // Categoría inexistente
            'brand_id' => 99999, // Marca inexistente
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($editor)->post('/admin/productos', $data);

        $response->assertSessionHasErrors(['sku', 'name', 'price', 'category_id', 'brand_id', 'status']);
    }

    public function test_editor_can_update_product_and_keep_own_sku(): void
    {
        $editor = User::where('email', 'editor@portatilesperu.com')->first();
        $product = Product::where('sku', 'LEN-LEG-PRO5-4060')->first();

        $data = [
            'sku' => 'LEN-LEG-PRO5-4060', // Mismo SKU
            'name' => 'Lenovo Legion Pro 5 (Edición Actualizada)',
            'price' => 6999.00,
            'currency' => 'PEN',
            'category_id' => $product->category_id,
            'brand_id' => $product->brand_id,
            'status' => 'published',
        ];

        $response = $this->actingAs($editor)->put("/admin/productos/{$product->id}", $data);

        $response->assertRedirect("/admin/productos/{$product->id}");
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Lenovo Legion Pro 5 (Edición Actualizada)',
            'price' => 6999.00,
        ]);
    }

    public function test_archived_products_are_excluded_from_published_scope(): void
    {
        // En el seeder existe un producto archivado: DELL-LAT-5440-ARC
        $archived = Product::where('sku', 'DELL-LAT-5440-ARC')->first();
        $this->assertEquals('archived', $archived->status);

        $publishedProducts = Product::published()->get();

        $this->assertFalse($publishedProducts->contains('id', $archived->id));
        $this->assertTrue($publishedProducts->every(fn ($p) => $p->status === 'published'));
    }

    public function test_image_validation_rejects_non_image_files(): void
    {
        $editor = User::where('email', 'editor@portatilesperu.com')->first();
        $category = Category::first();
        $brand = Brand::first();

        $data = [
            'sku' => 'TEST-BAD-IMG-001',
            'name' => 'Laptop con Archivo Malicioso',
            'price' => 2000.00,
            'currency' => 'PEN',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'status' => 'draft',
            'images' => [
                UploadedFile::fake()->create('malicious.pdf', 100, 'application/pdf'),
            ],
        ];

        $response = $this->actingAs($editor)->post('/admin/productos', $data);

        $response->assertSessionHasErrors('images.0');
    }

    public function test_user_without_catalog_manage_permission_cannot_create_or_delete_product(): void
    {
        // El auditor solo tiene permiso catalog.view, no catalog.manage
        $auditor = User::where('email', 'auditor@portatilesperu.com')->first();
        $product = Product::first();

        // Puede ver
        $this->actingAs($auditor)->get('/admin/productos')->assertStatus(200);
        $this->actingAs($auditor)->get("/admin/productos/{$product->id}")->assertStatus(200);

        // Intento de crear formulario -> 403
        $this->actingAs($auditor)->get('/admin/productos/crear')->assertStatus(403);

        // Intento de post -> 403
        $this->actingAs($auditor)->post('/admin/productos', [
            'sku' => 'HACK-SKU-001',
            'name' => 'Laptop No Autorizada',
        ])->assertStatus(403);

        // Intento de borrar -> 403
        $this->actingAs($auditor)->delete("/admin/productos/{$product->id}")->assertStatus(403);
    }

    public function test_quick_toggle_status_changes_product_visibility(): void
    {
        $editor = User::where('email', 'editor@portatilesperu.com')->first();
        $product = Product::where('status', 'draft')->first();

        $response = $this->actingAs($editor)->patch("/admin/productos/{$product->id}/estado", [
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $this->assertEquals('published', $product->fresh()->status);
    }

    public function test_editor_can_download_template_and_import_products_from_csv(): void
    {
        $editor = User::where('email', 'editor@portatilesperu.com')->first();
        $category = Category::first();
        $brand = Brand::first();
        $csv = implode("\n", [
            'sku,name,price,currency,category,brand,status,short_description,description,barcode,procesador,ram,almacenamiento,pantalla,grafica,sistema_operativo,conectividad,garantia,image_url_1,image_url_2,image_url_3',
            "CSV-IMPORT-001,Laptop importada,2899.00,PEN,{$category->name},{$brand->name},draft,Resumen,Descripcion,770000000001,Intel Core i5,16 GB,512 GB SSD,15.6 IPS,RTX 3050,Windows 11,Wi-Fi 6,12 meses,https://images.example.com/one.jpg,,",
        ]);

        $this->actingAs($editor)->get(route('admin.products.import-template'))
            ->assertOk()
            ->assertDownload('plantilla-productos.csv');

        $response = $this->actingAs($editor)->post(route('admin.products.import'), [
            'products_file' => UploadedFile::fake()->createWithContent('productos.csv', $csv),
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['sku' => 'CSV-IMPORT-001', 'status' => 'draft']);
        $product = Product::where('sku', 'CSV-IMPORT-001')->firstOrFail();
        $this->assertSame('Intel Core i5', $product->technical_specs['procesador']);
        $this->assertSame('https://images.example.com/one.jpg', $product->images()->firstOrFail()->url);
    }

    public function test_duplicate_ean_is_rejected_by_server_validation(): void
    {
        $editor = User::where('email', 'editor@portatilesperu.com')->first();
        $existingProduct = Product::first();
        $existingProduct->update(['barcode' => '770000000009']);

        $response = $this->actingAs($editor)->post(route('admin.products.store'), [
            'sku' => 'EAN-DUPLICATE-001',
            'name' => 'Laptop con EAN duplicado',
            'price' => 2000,
            'currency' => 'PEN',
            'category_id' => $existingProduct->category_id,
            'brand_id' => $existingProduct->brand_id,
            'status' => 'draft',
            'barcode' => '770000000009',
        ]);

        $response->assertSessionHasErrors('barcode');
        $this->assertDatabaseMissing('products', ['sku' => 'EAN-DUPLICATE-001']);
    }

    public function test_editor_can_view_excel_csv_template_guide_page(): void
    {
        $editor = User::where('email', 'editor@portatilesperu.com')->first();

        $response = $this->actingAs($editor)->get(route('admin.products.template-guide'));

        $response->assertOk();
        $response->assertSee('Plantilla Oficial para Carga Masiva de Laptops');
        $response->assertSee('Diccionario Detallado de Columnas');
        $response->assertSee('Previsualización del Archivo CSV / Excel');
        $response->assertSee(route('admin.products.import-template'));
    }

    public function test_editor_can_use_gemini_autofill_endpoint(): void
    {
        $editor = User::where('email', 'editor@portatilesperu.com')->first();

        $response = $this->actingAs($editor)->postJson(route('admin.products.gemini-autofill'), [
            'prompt' => 'Lenovo Legion Pro 5 16IRX9 Intel Core i7-14700HX 32GB RAM 1TB SSD RTX 4060',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'name',
                'sku',
                'short_description',
                'description',
                'technical_specs' => [
                    'procesador',
                    'ram',
                    'almacenamiento',
                    'grafica',
                ],
            ],
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_editor_can_save_gemini_api_key(): void
    {
        $editor = User::where('email', 'editor@portatilesperu.com')->first();

        $response = $this->actingAs($editor)->postJson(route('admin.products.save-gemini-key'), [
            'api_key' => 'AIzaSyFakeTestKeyForGeminiApi12345',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'has_key' => true,
        ]);

        $this->assertSame('AIzaSyFakeTestKeyForGeminiApi12345', Setting::getValue('gemini_api_key'));
    }

    public function test_editor_can_create_laptop_with_dynamic_url_images(): void
    {
        $editor = User::where('email', 'editor@portatilesperu.com')->first();
        $category = Category::first();
        $brand = Brand::first();

        $response = $this->actingAs($editor)->post(route('admin.products.store'), [
            'sku' => 'TEST-URL-LAP-001',
            'name' => 'Laptop con Múltiples URLs Dinámicas',
            'price' => 3899.00,
            'currency' => 'PEN',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'status' => 'published',
            'technical_specs' => [
                'procesador' => 'Intel Core i5',
            ],
            'image_urls' => [
                'https://images.example.com/dynamic-front.jpg',
                'https://images.example.com/dynamic-side.jpg',
                'https://images.example.com/dynamic-back.jpg',
                'https://images.example.com/dynamic-box.jpg',
            ],
        ]);

        $response->assertRedirect();
        $product = Product::where('sku', 'TEST-URL-LAP-001')->firstOrFail();
        $this->assertCount(4, $product->images);
        $this->assertSame('https://images.example.com/dynamic-front.jpg', $product->images->first()->url);
        $this->assertTrue((bool) $product->images->first()->is_primary);
    }
}
