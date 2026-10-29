<?php

namespace App\Http\Controllers;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    // ─── Panel principal de inventario ───────────────────────────────────────

    public function index(Request $request): View
    {
        $query = InventoryBalance::with(['product:id,name,sku,status', 'warehouse:id,name,code'])
            ->latest('last_movement_at');

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('stock_filter')) {
            match ($request->stock_filter) {
                'low' => $query->lowStock(),
                'out' => $query->outOfStock(),
                default => null,
            };
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
            );
        }

        $balances = $query->paginate(20)->withQueryString();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        $summary = [
            'total_products' => InventoryBalance::distinct('product_id')->count('product_id'),
            'total_physical' => InventoryBalance::sum('qty_physical'),
            'total_reserved' => InventoryBalance::sum('qty_reserved'),
            'total_available' => InventoryBalance::sum('qty_available'),
            'low_stock_count' => InventoryBalance::lowStock()->count(),
            'out_of_stock' => InventoryBalance::outOfStock()->count(),
        ];

        return view('admin.inventory.index', compact('balances', 'warehouses', 'summary'));
    }

    // ─── Detalle de stock de un producto ─────────────────────────────────────

    public function productStock(Product $product): View
    {
        $balances = $product->inventoryBalances()->with('warehouse')->get();
        $movements = StockMovement::forProduct($product->id)
            ->with(['warehouse:id,name,code', 'user:id,name'])
            ->latest('moved_at')
            ->paginate(30);

        $globalStock = $this->inventoryService->getGlobalStock($product->id);

        return view('admin.inventory.product_stock', compact('product', 'balances', 'movements', 'globalStock'));
    }

    // ─── Historial de movimientos ─────────────────────────────────────────────

    public function movements(Request $request): View
    {
        $query = StockMovement::with([
            'product:id,name,sku',
            'warehouse:id,name,code',
            'user:id,name',
        ])->latest('moved_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('moved_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('moved_at', '<=', $request->date_to);
        }

        $movements = $query->paginate(30)->withQueryString();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $types = array_keys(array_fill_keys(
            ['entry', 'exit', 'adjustment', 'transfer_in', 'transfer_out', 'return', 'reservation', 'release'],
            null
        ));

        return view('admin.inventory.movements', compact('movements', 'warehouses', 'types'));
    }

    // ─── Entrada de stock ────────────────────────────────────────────────────

    public function entryForm(): View
    {
        $products = Product::published()->orderBy('name')->get(['id', 'name', 'sku']);
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('admin.inventory.entry', compact('products', 'warehouses'));
    }

    public function storeEntry(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:entry,transfer_in,return',
            'reference' => 'nullable|string|max:80',
            'reason' => 'nullable|string|max:200',
            'document_number' => 'nullable|string|max:60',
            'unit_cost' => 'nullable|numeric|min:0',
        ]);

        $this->inventoryService->registerEntry(
            productId: $data['product_id'],
            warehouseId: $data['warehouse_id'],
            userId: auth()->id(),
            quantity: $data['quantity'],
            type: $data['type'],
            reference: $data['reference'] ?? null,
            reason: $data['reason'] ?? null,
            documentNumber: $data['document_number'] ?? null,
            unitCost: isset($data['unit_cost']) ? (float) $data['unit_cost'] : null,
        );

        return redirect()->route('admin.inventory.index')
            ->with('success', "Entrada de {$data['quantity']} unidades registrada correctamente.");
    }

    // ─── Salida de stock ──────────────────────────────────────────────────────

    public function exitForm(): View
    {
        $products = Product::published()->orderBy('name')->get(['id', 'name', 'sku']);
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('admin.inventory.exit', compact('products', 'warehouses'));
    }

    public function storeExit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:exit,transfer_out',
            'reference' => 'nullable|string|max:80',
            'reason' => 'nullable|string|max:200',
            'document_number' => 'nullable|string|max:60',
            'unit_cost' => 'nullable|numeric|min:0',
        ]);

        $this->inventoryService->registerExit(
            productId: $data['product_id'],
            warehouseId: $data['warehouse_id'],
            userId: auth()->id(),
            quantity: $data['quantity'],
            type: $data['type'],
            reference: $data['reference'] ?? null,
            reason: $data['reason'] ?? null,
            documentNumber: $data['document_number'] ?? null,
            unitCost: isset($data['unit_cost']) ? (float) $data['unit_cost'] : null,
        );

        return redirect()->route('admin.inventory.index')
            ->with('success', "Salida de {$data['quantity']} unidades registrada correctamente.");
    }

    // ─── Ajuste de inventario ─────────────────────────────────────────────────

    public function adjustmentForm(): View
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('admin.inventory.adjustment', compact('products', 'warehouses'));
    }

    public function storeAdjustment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'new_quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:200',
            'document_number' => 'nullable|string|max:60',
        ]);

        $this->inventoryService->registerAdjustment(
            productId: $data['product_id'],
            warehouseId: $data['warehouse_id'],
            userId: auth()->id(),
            newPhysicalQty: $data['new_quantity'],
            reason: $data['reason'],
            documentNumber: $data['document_number'] ?? null,
        );

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Ajuste de inventario registrado correctamente.');
    }

    // ─── Escaneo QR / Código de barras ───────────────────────────────────────

    /**
     * Vista de escáner (cámara del navegador o lector USB).
     * REGLA: escanear no mueve stock; solo busca el producto.
     */
    public function scannerView(): View
    {
        return view('admin.inventory.scanner');
    }

    /**
     * Busca un producto por SKU, QR o código de barras (AJAX).
     * No modifica ningún dato.
     */
    public function scanLookup(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255',
        ]);

        $code = trim($request->code);
        $product = null;

        // Si el código escaneado es una URL completa (ej. generada por el QR o cámara)
        if (str_contains($code, '/catalogo/')) {
            $parsedUrl = parse_url($code);

            // Intentar por parámetro ?sku= si existe
            if (! empty($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $queryParams);
                if (! empty($queryParams['sku'])) {
                    $product = Product::where('sku', trim($queryParams['sku']))->first();
                }
            }

            // Si no se encontró por query param, extraer el slug de la ruta /catalogo/{slug}
            if (! $product && ! empty($parsedUrl['path'])) {
                $pathParts = array_filter(explode('/', trim($parsedUrl['path'], '/')));
                $slug = end($pathParts);
                if ($slug && $slug !== 'catalogo') {
                    $product = Product::where('slug', $slug)->first();
                }
            }
        }

        // Búsqueda directa por SKU, Código de barras, QR Code o Slug
        if (! $product) {
            $product = Product::where('sku', $code)
                ->orWhere('barcode', $code)
                ->orWhere('qr_code', $code)
                ->orWhere('slug', $code)
                ->first();
        }

        if (! $product) {
            return response()->json(['found' => false, 'message' => 'Producto no encontrado.'], 404);
        }

        $product->loadMissing([
            'brand:id,name',
            'category:id,name',
            'inventoryBalances.warehouse:id,name,code',
        ]);

        $globalStock = $this->inventoryService->getGlobalStock($product->id);

        return response()->json([
            'found' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'brand' => $product->brand?->name,
                'category' => $product->category?->name,
                'price' => $product->formatted_price,
                'status' => $product->status,
                'qr_code_url' => route('admin.qr.svg', $product->id),
                'main_image' => $product->primary_image_url,
                'detail_url' => route('admin.inventory.product', $product->id),
            ],
            'stock' => $globalStock,
        ]);
    }

    // ─── Reservas ─────────────────────────────────────────────────────────────

    public function reservations(Request $request): View
    {
        $reservations = StockReservation::with([
            'product:id,name,sku',
            'warehouse:id,name,code',
            'user:id,name',
        ])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.inventory.reservations', compact('reservations'));
    }

    public function cancelReservation(Request $request, int $reservationId): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:200',
        ]);

        $this->inventoryService->cancelReservation(
            reservationId: $reservationId,
            cancelledBy: auth()->id(),
            reason: $request->reason,
        );

        return back()->with('success', "Reserva #{$reservationId} cancelada y stock liberado.");
    }
}
