<?php

namespace Database\Seeders;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

/**
 * InventorySeeder — Crea almacenes y stock inicial para pruebas (Fase 3).
 *
 * Los datos son ficticios para desarrollo; no representan información comercial real.
 * El stock inicial se registra como movimientos de tipo 'entry' para mantener la
 * trazabilidad desde el primer día.
 */
class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. Almacenes ─────────────────────────────────────────────────────
        $warehouses = [
            [
                'name' => 'Almacén Principal Lima',
                'code' => 'LIMA-01',
                'address' => 'Av. La Marina 1234, San Miguel',
                'city' => 'Lima',
                'responsible_name' => 'Jorge Huamán',
                'phone' => '+51 987 654 324',
                'is_active' => true,
                'notes' => 'Almacén central con mayor volumen de rotación.',
            ],
            [
                'name' => 'Almacén Showroom Miraflores',
                'code' => 'MIRAF-01',
                'address' => 'Av. Larco 500, Miraflores',
                'city' => 'Lima',
                'responsible_name' => 'Sofía Díaz',
                'phone' => '+51 987 654 325',
                'is_active' => true,
                'notes' => 'Showroom y punto de venta directo. Stock limitado.',
            ],
            [
                'name' => 'Almacén Tránsito',
                'code' => 'TRANS-01',
                'address' => null,
                'city' => 'Lima',
                'responsible_name' => null,
                'phone' => null,
                'is_active' => true,
                'notes' => 'Almacén temporal para mercancía en tránsito o devoluciones.',
            ],
        ];

        $createdWarehouses = [];
        foreach ($warehouses as $data) {
            $createdWarehouses[$data['code']] = Warehouse::firstOrCreate(
                ['code' => $data['code']],
                $data
            );
        }

        // ─── 2. Buscar usuario "almacen" para registrar movimientos iniciales ──
        $warehouseUser = User::where('email', 'almacen@portatilesperu.com')->first();
        $adminUser = User::where('email', 'superadmin@portatilesperu.com')->first();
        $userId = $warehouseUser?->id ?? $adminUser?->id ?? 1;

        // ─── 3. Stock inicial por producto (aleatorio para pruebas) ───────────
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->warn('No se encontraron productos. Ejecuta primero CatalogSeeder.');

            return;
        }

        $mainWarehouse = $createdWarehouses['LIMA-01'];
        $showroomWarehouse = $createdWarehouses['MIRAF-01'];

        foreach ($products as $product) {
            // Stock aleatorio realista para el almacén principal
            $physicalMain = rand(0, 15);
            $physicalShowroom = rand(0, 5);
            $minimumMain = rand(1, 3);

            // ─ Almacén principal ─
            $existingMain = InventoryBalance::where('product_id', $product->id)
                ->where('warehouse_id', $mainWarehouse->id)
                ->first();

            if (! $existingMain && $physicalMain > 0) {
                $balance = InventoryBalance::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $mainWarehouse->id,
                    'qty_physical' => $physicalMain,
                    'qty_reserved' => 0,
                    'qty_available' => $physicalMain,
                    'qty_minimum' => $minimumMain,
                    'last_movement_at' => now(),
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $mainWarehouse->id,
                    'user_id' => $userId,
                    'type' => 'entry',
                    'quantity' => $physicalMain,
                    'qty_before' => 0,
                    'qty_after' => $physicalMain,
                    'reason' => 'Stock inicial de apertura (seeder)',
                    'reference' => 'STOCK-INICIAL-2026',
                    'moved_at' => now()->subDays(rand(1, 30)),
                ]);
            }

            // ─ Showroom ─
            $existingShowroom = InventoryBalance::where('product_id', $product->id)
                ->where('warehouse_id', $showroomWarehouse->id)
                ->first();

            if (! $existingShowroom && $physicalShowroom > 0) {
                InventoryBalance::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $showroomWarehouse->id,
                    'qty_physical' => $physicalShowroom,
                    'qty_reserved' => 0,
                    'qty_available' => $physicalShowroom,
                    'qty_minimum' => 1,
                    'last_movement_at' => now(),
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $showroomWarehouse->id,
                    'user_id' => $userId,
                    'type' => 'entry',
                    'quantity' => $physicalShowroom,
                    'qty_before' => 0,
                    'qty_after' => $physicalShowroom,
                    'reason' => 'Stock inicial showroom (seeder)',
                    'reference' => 'STOCK-INICIAL-2026',
                    'moved_at' => now()->subDays(rand(1, 15)),
                ]);
            }
        }

        $this->command->info('InventorySeeder completado: '.count($warehouses).' almacenes y stock inicial para '.$products->count().' productos.');
    }
}
