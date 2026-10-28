<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPdfTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $permission = Permission::create(['name' => 'catalog.pdf', 'display_name' => 'PDF', 'module' => 'catalogo']);
        $role = Role::create(['name' => 'pdf-editor', 'display_name' => 'Editor PDF']);
        $role->permissions()->attach($permission);
        $this->user = User::factory()->create(['status' => 'active']);
        $this->user->roles()->attach($role);
        $category = Category::create(['name' => 'Ultrabooks', 'slug' => 'ultrabooks']);
        $brand = Brand::create(['name' => 'Lenovo', 'slug' => 'lenovo']);
        $this->product = Product::create(['name' => 'ThinkPad X1', 'sku' => 'TP-X1', 'category_id' => $category->id, 'brand_id' => $brand->id, 'price' => 4999, 'status' => 'published']);
    }

    public function test_authorized_user_can_open_generator_and_generate_pdf(): void
    {
        $this->actingAs($this->user)->get(route('admin.catalog-pdf.index'))->assertOk()->assertSee('Diseña, previsualiza y descarga tu catálogo');
        $this->actingAs($this->user)->post(route('admin.catalog-pdf.generate'), ['template' => 'tecnico', 'paper' => 'a4', 'orientation' => 'portrait', 'product_ids' => [$this->product->id], 'show_prices' => 1])->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_pdf_generator_never_includes_unpublished_products(): void
    {
        $this->product->update(['status' => 'draft']);
        $this->actingAs($this->user)->post(route('admin.catalog-pdf.generate'), ['template' => 'minimalista', 'paper' => 'a4', 'orientation' => 'portrait', 'product_ids' => [$this->product->id]])->assertStatus(422);
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create(['status' => 'active']))->get(route('admin.catalog-pdf.index'))->assertForbidden();
    }

    public function test_authorized_user_can_export_selected_catalog_as_excel_compatible_csv(): void
    {
        $response = $this->actingAs($this->user)->post(route('admin.catalog-pdf.generate'), [
            'template' => 'comercial',
            'paper' => 'letter',
            'orientation' => 'landscape',
            'category_ids' => [$this->product->category_id],
            'brand_ids' => [$this->product->brand_id],
            'format' => 'csv',
        ]);

        $response->assertOk()->assertDownload();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
