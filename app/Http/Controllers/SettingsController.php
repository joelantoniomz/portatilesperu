<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductModel;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Models\Subcategory;
use App\Models\Warehouse;
use App\Models\WhatsappWebhookEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = Setting::query()->pluck('value', 'key');

        $counts = [
            'products' => Product::count(),
            'orders' => Order::count(),
            'movements' => StockMovement::count(),
            'conversations' => Conversation::count(),
            'customers' => Customer::count(),
            'categories' => Category::count(),
            'brands' => Brand::count(),
        ];

        return view('admin.settings.index', compact('settings', 'counts'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:120'],
            'company_logo_url' => ['nullable', 'url', 'max:2048'],
            'company_logo_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'theme_color' => ['required', 'in:emerald,blue,violet'],
        ]);

        if ($request->hasFile('company_logo_file')) {
            $path = $request->file('company_logo_file')->store('logos', 'public');
            $data['company_logo_url'] = asset('storage/'.$path);
        }

        unset($data['company_logo_file']);

        foreach ($data as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        AuditLog::log('settings_updated', 'settings', null, null, [
            'company_name' => $data['company_name'],
            'has_logo' => ! empty($data['company_logo_url']),
        ]);

        return back()->with('status', 'Configuración guardada correctamente.');
    }

    /**
     * Restablece el sistema de fábrica (deja todo en cero según las opciones elegidas).
     * Mantiene intactos a los usuarios, roles y permisos para no perder acceso administrativo.
     */
    public function factoryReset(Request $request): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'string'],
        ]);

        if (strtoupper(trim($request->confirmation)) !== 'RESTABLECER') {
            return back()->with('error', 'Palabra de confirmación incorrecta. Debe escribir exactamente "RESTABLECER" en mayúsculas.');
        }

        $resetCatalog = $request->boolean('reset_catalog', true);
        $resetOrders = $request->boolean('reset_orders', true);
        $resetChats = $request->boolean('reset_chats', true);
        $resetTaxonomies = $request->boolean('reset_taxonomies', false);

        DB::transaction(function () use ($resetCatalog, $resetOrders, $resetChats, $resetTaxonomies): void {
            $driver = DB::getDriverName();

            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            } elseif ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            // 1. Pedidos y ventas
            if ($resetOrders) {
                OrderStatusHistory::query()->delete();
                OrderItem::query()->delete();
                Order::query()->delete();
            }

            // 2. Chats de WhatsApp y clientes
            if ($resetChats) {
                ConversationMessage::query()->delete();
                Conversation::query()->delete();
                WhatsappWebhookEvent::query()->delete();
                CustomerNote::query()->delete();
                Customer::query()->delete();
            }

            // 3. Catálogo e Inventario
            if ($resetCatalog) {
                StockMovement::query()->delete();
                StockReservation::query()->delete();
                InventoryBalance::query()->delete();

                // Eliminar archivos físicos de imágenes del catálogo
                $images = ProductImage::query()->get();
                foreach ($images as $img) {
                    if ($img->file_path && ! str_starts_with($img->file_path, 'http')) {
                        Storage::disk('public')->delete($img->file_path);
                    }
                }
                ProductImage::query()->delete();
                Product::query()->delete();
            }

            // 4. Categorías y marcas si se solicitó
            if ($resetTaxonomies) {
                ProductModel::query()->delete();
                Subcategory::query()->delete();
                Category::query()->delete();
                Brand::query()->delete();
            }

            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            } elseif ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        });

        // Limpiar todas las cachés
        cache()->flush();

        AuditLog::log('system_factory_reset', 'settings', null, null, [
            'reset_catalog' => $resetCatalog,
            'reset_orders' => $resetOrders,
            'reset_chats' => $resetChats,
            'reset_taxonomies' => $resetTaxonomies,
        ]);

        return redirect()->route('admin.settings.index')
            ->with('status', '¡Restablecimiento de fábrica completado con éxito! Los datos seleccionados han sido borrados y todos los contadores del sistema están en cero.');
    }

    /**
     * Exporta una copia de seguridad completa del catálogo, configuración e inventario en formato JSON.
     */
    public function exportBackup(): StreamedResponse
    {
        $backupData = [
            'meta' => [
                'app' => 'Portátiles Perú',
                'version' => '1.0',
                'exported_at' => now()->toIso8601String(),
                'company' => Setting::getValue('company_name', 'Portátiles Perú'),
            ],
            'settings' => Setting::query()->pluck('value', 'key')->all(),
            'categories' => Category::with('subcategories')->get()->toArray(),
            'brands' => Brand::with('models')->get()->toArray(),
            'products' => Product::with(['images', 'category:id,name', 'brand:id,name'])->get()->toArray(),
            'inventory' => InventoryBalance::with(['product:id,sku', 'warehouse:id,code'])->get()->toArray(),
        ];

        $filename = 'backup-portatilesperu-'.now()->format('Y-m-d-His').'.json';

        return response()->streamDownload(function () use ($backupData): void {
            echo json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    /**
     * Restaura una copia de seguridad JSON previamente exportada.
     */
    public function importBackup(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'max:20480'],
        ]);

        $content = file_get_contents($request->file('backup_file')->getRealPath());
        $data = json_decode($content, true);

        if (! is_array($data) || ! isset($data['products'])) {
            return back()->with('error', 'El archivo no tiene el formato de copia de seguridad válido de Portátiles Perú.');
        }

        $restoredProducts = 0;

        DB::transaction(function () use ($data, &$restoredProducts): void {
            // 1. Restaurar Categorías
            $categoryMap = [];
            if (! empty($data['categories']) && is_array($data['categories'])) {
                foreach ($data['categories'] as $cat) {
                    $category = Category::firstOrCreate(
                        ['name' => $cat['name']],
                        [
                            'slug' => $cat['slug'] ?? Str::slug($cat['name']),
                            'description' => $cat['description'] ?? null,
                            'is_active' => $cat['is_active'] ?? true,
                        ]
                    );
                    $categoryMap[$cat['id']] = $category->id;

                    if (! empty($cat['subcategories']) && is_array($cat['subcategories'])) {
                        foreach ($cat['subcategories'] as $sub) {
                            Subcategory::firstOrCreate(
                                ['category_id' => $category->id, 'name' => $sub['name']],
                                [
                                    'slug' => $sub['slug'] ?? Str::slug($sub['name']),
                                    'is_active' => $sub['is_active'] ?? true,
                                ]
                            );
                        }
                    }
                }
            }

            // 2. Restaurar Marcas
            $brandMap = [];
            if (! empty($data['brands']) && is_array($data['brands'])) {
                foreach ($data['brands'] as $br) {
                    $brand = Brand::firstOrCreate(
                        ['name' => $br['name']],
                        [
                            'slug' => $br['slug'] ?? Str::slug($br['name']),
                            'country' => $br['country'] ?? 'Internacional',
                            'is_active' => $br['is_active'] ?? true,
                        ]
                    );
                    $brandMap[$br['id']] = $brand->id;

                    if (! empty($br['models']) && is_array($br['models'])) {
                        foreach ($br['models'] as $m) {
                            ProductModel::firstOrCreate(
                                ['brand_id' => $brand->id, 'name' => $m['name']],
                                ['is_active' => $m['is_active'] ?? true]
                            );
                        }
                    }
                }
            }

            // 3. Restaurar Productos e Imágenes
            if (! empty($data['products']) && is_array($data['products'])) {
                $defaultCategory = Category::first();
                $defaultBrand = Brand::first();

                foreach ($data['products'] as $prod) {
                    $categoryId = $categoryMap[$prod['category_id'] ?? 0] ?? $defaultCategory?->id;
                    $brandId = $brandMap[$prod['brand_id'] ?? 0] ?? $defaultBrand?->id;

                    if (! $categoryId || ! $brandId) {
                        continue;
                    }

                    $product = Product::updateOrCreate(
                        ['sku' => $prod['sku']],
                        [
                            'name' => $prod['name'],
                            'slug' => $prod['slug'] ?? Str::slug($prod['name'].'-'.$prod['sku']),
                            'category_id' => $categoryId,
                            'brand_id' => $brandId,
                            'short_description' => $prod['short_description'] ?? null,
                            'description' => $prod['description'] ?? null,
                            'price' => $prod['price'] ?? 0,
                            'currency' => $prod['currency'] ?? 'PEN',
                            'barcode' => $prod['barcode'] ?? null,
                            'qr_code' => $prod['qr_code'] ?? 'PORT-'.strtoupper($prod['sku']),
                            'status' => $prod['status'] ?? 'published',
                            'is_featured' => $prod['is_featured'] ?? false,
                            'is_new' => $prod['is_new'] ?? false,
                            'min_stock_alert' => $prod['min_stock_alert'] ?? 2,
                            'weight' => $prod['weight'] ?? null,
                            'dimensions' => $prod['dimensions'] ?? null,
                            'technical_specs' => $prod['technical_specs'] ?? [],
                        ]
                    );

                    // Restaurar imágenes
                    if (! empty($prod['images']) && is_array($prod['images'])) {
                        foreach ($prod['images'] as $img) {
                            ProductImage::firstOrCreate(
                                ['product_id' => $product->id, 'file_path' => $img['file_path']],
                                [
                                    'is_primary' => $img['is_primary'] ?? false,
                                    'sort_order' => $img['sort_order'] ?? 0,
                                ]
                            );
                        }
                    }

                    // Asegurar balance inicial en almacén central
                    $centralWarehouse = Warehouse::where('code', 'LIMA-01')->first() ?? Warehouse::first();
                    if ($centralWarehouse) {
                        InventoryBalance::firstOrCreate(
                            ['product_id' => $product->id, 'warehouse_id' => $centralWarehouse->id],
                            ['qty_physical' => 5, 'qty_reserved' => 0]
                        );
                    }

                    $restoredProducts++;
                }
            }
        });

        cache()->flush();

        AuditLog::log('backup_imported', 'settings', null, null, [
            'products_restored' => $restoredProducts,
        ]);

        return redirect()->route('admin.settings.index')
            ->with('status', "Copia de seguridad restaurada correctamente. Se restauraron {$restoredProducts} productos al catálogo.");
    }
}
