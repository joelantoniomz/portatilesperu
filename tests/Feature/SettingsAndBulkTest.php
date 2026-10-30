<?php

namespace Tests\Feature;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\InventorySeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsAndBulkTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(CatalogSeeder::class);
        $this->seed(InventorySeeder::class);

        $this->adminUser = User::where('email', 'admin@portatilesperu.com')->first();
    }

    /**
     * Prueba que se pueden eliminar productos de forma masiva (bulk delete).
     */
    public function test_bulk_delete_products_removes_them_and_their_balances(): void
    {
        $products = Product::take(2)->get();
        $ids = $products->pluck('id')->all();

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.products.bulk-action'), [
                'action' => 'delete',
                'product_ids' => $ids,
            ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('status');

        foreach ($ids as $id) {
            $this->assertDatabaseMissing('products', ['id' => $id]);
            $this->assertDatabaseMissing('inventory_balances', ['product_id' => $id]);
        }
    }

    /**
     * Prueba que se pueden archivar productos de forma masiva.
     */
    public function test_bulk_archive_products_changes_status_to_archived(): void
    {
        $products = Product::take(2)->get();
        $ids = $products->pluck('id')->all();

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.products.bulk-action'), [
                'action' => 'archive',
                'product_ids' => $ids,
            ]);

        $response->assertRedirect(route('admin.products.index'));

        foreach ($ids as $id) {
            $this->assertDatabaseHas('products', [
                'id' => $id,
                'status' => 'archived',
            ]);
        }
    }

    /**
     * Prueba la exportación de la copia de seguridad JSON.
     */
    public function test_export_backup_returns_json_download(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.settings.backup.export'));

        $response->assertOk();
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition') ?? '');
    }

    /**
     * Prueba el restablecimiento de fábrica (deja los contadores en cero).
     */
    public function test_factory_reset_wipes_catalog_and_resets_counts(): void
    {
        $this->assertGreaterThan(0, Product::count());

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.settings.factory-reset'), [
                'confirmation' => 'RESTABLECER',
                'reset_catalog' => '1',
                'reset_orders' => '1',
                'reset_chats' => '1',
            ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('status');

        $this->assertEquals(0, Product::count());
        $this->assertEquals(0, InventoryBalance::count());

        // Los usuarios administradores deben seguir existiendo
        $this->assertDatabaseHas('users', ['email' => 'admin@portatilesperu.com']);
    }

    /**
     * Prueba que el restablecimiento de fábrica requiere la palabra exacta RESTABLECER.
     */
    public function test_factory_reset_fails_without_exact_confirmation(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.settings.factory-reset'), [
                'confirmation' => 'incorrecto',
            ]);

        $response->assertSessionHas('error');
        $this->assertGreaterThan(0, Product::count());
    }

    /**
     * Prueba que el helper de WhatsApp genera la URL correcta con mensaje y número dinámico.
     */
    public function test_whatsapp_helper_uses_settings_number(): void
    {
        Setting::updateOrCreate(['key' => 'whatsapp_number'], ['value' => '+51 987 654 321']);

        $number = Setting::getWhatsappNumber();
        $this->assertEquals('51987654321', $number);

        $url = Setting::getWhatsappUrl('Hola prueba');
        $this->assertStringContainsString('https://wa.me/51987654321?text=Hola+prueba', $url);
    }
}
