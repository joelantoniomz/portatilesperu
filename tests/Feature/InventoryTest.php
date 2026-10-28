<?php

namespace Tests\Feature;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\InventorySeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $sellerUser;

    private Product $product;

    private Warehouse $warehouseCentral;

    private Warehouse $warehouseBranch;

    private InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(CatalogSeeder::class);
        $this->seed(InventorySeeder::class);

        $this->adminUser = User::where('email', 'admin@portatilesperu.com')->first();
        $this->sellerUser = User::where('email', 'vendedor@portatilesperu.com')->first();

        $this->product = Product::first();
        $this->warehouseCentral = Warehouse::where('code', 'LIMA-01')->first();
        $this->warehouseBranch = Warehouse::where('code', 'MIRAF-01')->first();

        $this->inventoryService = app(InventoryService::class);
    }

    /**
     * Test 1: Entrada de stock incrementa balance físico y disponible y crea movimiento.
     */
    public function test_entry_increases_physical_and_available_stock_and_creates_movement(): void
    {
        $initialBalance = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseCentral->id)
            ->first();

        $initialPhysical = $initialBalance ? $initialBalance->qty_physical : 0;
        $initialAvailable = $initialBalance ? $initialBalance->qty_available : 0;

        $response = $this->actingAs($this->adminUser)
            ->post('/admin/inventario/entrada', [
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouseCentral->id,
                'quantity' => 5,
                'type' => 'entry',
                'reference' => 'TEST-OC-01',
                'reason' => 'Ingreso de prueba por compra',
                'document_number' => 'FAC-001',
                'unit_cost' => 2500.00,
            ]);

        $response->assertRedirect('/admin/inventario');
        $response->assertSessionHas('success');

        $updatedBalance = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseCentral->id)
            ->first();

        $this->assertEquals($initialPhysical + 5, $updatedBalance->qty_physical);
        $this->assertEquals($initialAvailable + 5, $updatedBalance->qty_available);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseCentral->id,
            'type' => 'entry',
            'quantity' => 5,
            'reference' => 'TEST-OC-01',
        ]);
    }

    /**
     * Test 2: Salida de stock falla si el stock disponible es insuficiente.
     */
    public function test_exit_fails_when_stock_is_insufficient(): void
    {
        $balance = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseCentral->id)
            ->first();

        $available = $balance ? $balance->qty_available : 0;
        $excessiveQty = $available + 1000;

        $this->expectException(ValidationException::class);

        $this->inventoryService->registerExit(
            productId: $this->product->id,
            warehouseId: $this->warehouseCentral->id,
            userId: $this->adminUser->id,
            quantity: $excessiveQty,
            type: 'exit',
            reason: 'Intento de sobreventa',
        );
    }

    /**
     * Test 3: Salida exitosa descuenta stock físico y disponible.
     */
    public function test_exit_decreases_stock_when_sufficient(): void
    {
        $this->inventoryService->registerEntry(
            productId: $this->product->id,
            warehouseId: $this->warehouseCentral->id,
            userId: $this->adminUser->id,
            quantity: 10,
            type: 'entry',
        );

        $balanceBefore = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseCentral->id)
            ->first();

        $this->inventoryService->registerExit(
            productId: $this->product->id,
            warehouseId: $this->warehouseCentral->id,
            userId: $this->adminUser->id,
            quantity: 3,
            type: 'exit',
            reason: 'Venta directa en mostrador',
        );

        $balanceAfter = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseCentral->id)
            ->first();

        $this->assertEquals($balanceBefore->qty_physical - 3, $balanceAfter->qty_physical);
        $this->assertEquals($balanceBefore->qty_available - 3, $balanceAfter->qty_available);
    }

    /**
     * Test 4: Reserva bloquea qty_available pero conserva qty_physical intacta.
     */
    public function test_reservation_locks_available_stock_without_reducing_physical(): void
    {
        $this->inventoryService->registerEntry(
            productId: $this->product->id,
            warehouseId: $this->warehouseCentral->id,
            userId: $this->adminUser->id,
            quantity: 10,
            type: 'entry',
        );

        $balanceBefore = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseCentral->id)
            ->first();

        $reservation = $this->inventoryService->createReservation(
            productId: $this->product->id,
            warehouseId: $this->warehouseCentral->id,
            userId: $this->adminUser->id,
            quantity: 2,
            orderReference: 'PED-TEST-001',
            notes: 'Reserva de prueba',
            expiresAt: now()->addHour()
        );

        $balanceAfter = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseCentral->id)
            ->first();

        $this->assertEquals($balanceBefore->qty_physical, $balanceAfter->qty_physical);
        $this->assertEquals($balanceBefore->qty_reserved + 2, $balanceAfter->qty_reserved);
        $this->assertEquals($balanceBefore->qty_available - 2, $balanceAfter->qty_available);
        $this->assertEquals('pending', $reservation->status);
    }

    /**
     * Test 5: Cancelar reserva libera qty_available y conserva el historial inmutable.
     */
    public function test_cancel_reservation_releases_available_stock_and_preserves_records(): void
    {
        $this->inventoryService->registerEntry(
            productId: $this->product->id,
            warehouseId: $this->warehouseCentral->id,
            userId: $this->adminUser->id,
            quantity: 10,
            type: 'entry',
        );

        $reservation = $this->inventoryService->createReservation(
            productId: $this->product->id,
            warehouseId: $this->warehouseCentral->id,
            userId: $this->adminUser->id,
            quantity: 2,
            orderReference: 'PED-TEST-002',
            notes: 'Reserva para cancelar',
            expiresAt: now()->addMinutes(30)
        );

        $balanceDuring = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseCentral->id)
            ->first();

        $this->inventoryService->cancelReservation(
            reservationId: $reservation->id,
            cancelledBy: $this->adminUser->id,
            reason: 'Cliente desistió de la compra'
        );

        $balanceAfter = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseCentral->id)
            ->first();

        $this->assertEquals($balanceDuring->qty_reserved - 2, $balanceAfter->qty_reserved);
        $this->assertEquals($balanceDuring->qty_available + 2, $balanceAfter->qty_available);

        $reservation->refresh();
        $this->assertEquals('cancelled', $reservation->status);
    }

    /**
     * Test 6: Escaneo por código es exclusivamente de consulta y NUNCA modifica stock.
     */
    public function test_scan_lookup_returns_product_and_never_modifies_stock(): void
    {
        $balanceBefore = InventoryBalance::where('product_id', $this->product->id)->sum('qty_physical');
        $movementsCountBefore = StockMovement::count();

        $response = $this->actingAs($this->adminUser)
            ->getJson("/admin/inventario/buscar-codigo?code={$this->product->sku}");

        $response->assertOk();
        $response->assertJson([
            'found' => true,
            'product' => [
                'sku' => $this->product->sku,
            ],
        ]);

        $balanceAfter = InventoryBalance::where('product_id', $this->product->id)->sum('qty_physical');
        $movementsCountAfter = StockMovement::count();

        $this->assertEquals($balanceBefore, $balanceAfter, 'El escaneo NO debe modificar el stock físico');
        $this->assertEquals($movementsCountBefore, $movementsCountAfter, 'El escaneo NO debe crear movimientos');
    }

    /**
     * Test 7: Traslado entre almacenes decrementa origen e incrementa destino en una transacción.
     */
    public function test_transfer_between_warehouses(): void
    {
        $this->inventoryService->registerEntry(
            productId: $this->product->id,
            warehouseId: $this->warehouseCentral->id,
            userId: $this->adminUser->id,
            quantity: 10,
            type: 'entry',
        );

        $originBefore = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseCentral->id)
            ->first();

        $destBefore = InventoryBalance::firstOrCreate(
            ['product_id' => $this->product->id, 'warehouse_id' => $this->warehouseBranch->id],
            ['qty_physical' => 0, 'qty_reserved' => 0, 'qty_available' => 0]
        );

        $this->inventoryService->transferBetweenWarehouses(
            productId: $this->product->id,
            fromWarehouseId: $this->warehouseCentral->id,
            toWarehouseId: $this->warehouseBranch->id,
            userId: $this->adminUser->id,
            quantity: 4,
            reason: 'Abastecimiento sucursal Miraflores'
        );

        $originAfter = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseCentral->id)
            ->first();

        $destAfter = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseBranch->id)
            ->first();

        $this->assertEquals($originBefore->qty_physical - 4, $originAfter->qty_physical);
        $this->assertEquals($destBefore->qty_physical + 4, $destAfter->qty_physical);
    }

    /**
     * Test 8: Ajuste físico actualiza cantidad exacta y genera registro compensatorio.
     */
    public function test_adjustment_sets_exact_counted_quantity(): void
    {
        $this->inventoryService->registerAdjustment(
            productId: $this->product->id,
            warehouseId: $this->warehouseCentral->id,
            userId: $this->adminUser->id,
            newPhysicalQty: 25,
            reason: 'Conteo físico general anual'
        );

        $balance = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseCentral->id)
            ->first();

        $this->assertEquals(25, $balance->qty_physical);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseCentral->id,
            'type' => 'adjustment',
            'reason' => 'Conteo físico general anual',
        ]);
    }

    /**
     * Test 9: Vendedor (sin permiso inventory.manage) puede ver stock pero NO registrar movimientos.
     */
    public function test_user_without_inventory_manage_cannot_record_movements(): void
    {
        // El vendedor tiene inventory.view pero no inventory.manage
        $responseView = $this->actingAs($this->sellerUser)
            ->get('/admin/inventario');
        $responseView->assertOk();

        // Intento de entrada prohibido (403)
        $responseEntry = $this->actingAs($this->sellerUser)
            ->post('/admin/inventario/entrada', [
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouseCentral->id,
                'quantity' => 1,
                'type' => 'entry',
            ]);
        $responseEntry->assertForbidden();
    }
}
