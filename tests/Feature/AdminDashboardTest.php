<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\InventorySeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $sellerUser;

    private User $auditorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(CatalogSeeder::class);
        $this->seed(InventorySeeder::class);

        $this->adminUser = User::where('email', 'admin@portatilesperu.com')->first();
        $this->sellerUser = User::where('email', 'vendedor@portatilesperu.com')->first();
        $this->auditorUser = User::where('email', 'auditor@portatilesperu.com')->first();
    }

    /**
     * Test 1: Invitado es redirigido a login al intentar entrar al dashboard.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    /**
     * Test 2: Usuario autenticado accede al dashboard y ve datos reales y verificables.
     */
    public function test_authenticated_admin_can_view_dashboard_with_real_metrics(): void
    {
        $totalProducts = Product::count();
        $publishedProducts = Product::where('status', 'published')->count();
        $totalPhysical = (int) InventoryBalance::sum('qty_physical');

        $response = $this->actingAs($this->adminUser)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Panel de Control');
        $response->assertSee('Catálogo de Laptops');
        $response->assertSee((string) $totalProducts);
        $response->assertSee((string) $totalPhysical);
        $response->assertSee('publicadas en web');
    }

    /**
     * Test 3: El dashboard calcula y muestra la valorización del inventario en soles.
     */
    public function test_dashboard_displays_inventory_valuation(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Valorización Inventario');
        $response->assertSee('S/');
    }

    /**
     * Test 4: El dashboard muestra distribución por sedes físicas (Almacén Central Lima, etc.).
     */
    public function test_dashboard_displays_warehouse_stock_distribution(): void
    {
        $warehouse = Warehouse::first();

        $response = $this->actingAs($this->adminUser)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Distribución de Stock por Sede');
        $response->assertSee($warehouse->name);
        $response->assertSee($warehouse->code);
    }

    /**
     * Test 5: El dashboard muestra movimientos recientes de Kardex en vivo.
     */
    public function test_dashboard_displays_recent_stock_movements(): void
    {
        $product = Product::first();
        $warehouse = Warehouse::first();

        StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'user_id' => $this->adminUser->id,
            'type' => 'entry',
            'quantity' => 7,
            'qty_before' => 0,
            'qty_after' => 7,
            'reference' => 'DASH-TEST-REF',
            'reason' => 'Ingreso para prueba de dashboard',
            'moved_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Últimos Movimientos de Stock');
        $response->assertSee('+7');
        $response->assertSee($product->name);
    }

    /**
     * Test 6: Accesos directos respetan los permisos de rol del usuario.
     */
    public function test_dashboard_actions_respect_user_permissions(): void
    {
        // Admin tiene catalog.manage -> debe ver "Nueva Laptop"
        $responseAdmin = $this->actingAs($this->adminUser)->get('/admin');
        $responseAdmin->assertSee('Nueva Laptop');
        $responseAdmin->assertSee('Entrada Stock');

        // Auditor (solo lectura) NO tiene catalog.manage ni inventory.manage
        $responseAuditor = $this->actingAs($this->auditorUser)->get('/admin');
        $responseAuditor->assertDontSee('Nueva Laptop');
        $responseAuditor->assertDontSee('Entrada Stock');
    }

    /**
     * Test 7: El dashboard muestra eventos recientes de auditoría del sistema.
     */
    public function test_dashboard_displays_recent_audit_logs(): void
    {
        AuditLog::log(
            'dashboard.test.action',
            'Product',
            1,
            null,
            ['sample' => 'data'],
            $this->adminUser->id
        );

        $response = $this->actingAs($this->adminUser)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Auditoría del Sistema en Vivo');
        $response->assertSee('dashboard.test.action');
    }
}
