<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Pantalla de inicio del panel administrativo (Fase 5: Dashboard con datos reales de catálogo e inventario).
     */
    public function index(): View
    {
        $user = auth()->user()->load('roles.permissions');

        $recentAudit = AuditLog::with('user')
            ->latest('id')
            ->limit(6)
            ->get();

        $recentMovements = StockMovement::with(['product:id,name,sku', 'warehouse:id,name,code', 'user:id,name'])
            ->latest('moved_at')
            ->limit(6)
            ->get();

        $lowStockBalances = InventoryBalance::lowStock()
            ->with(['product:id,name,sku,status', 'warehouse:id,name,code'])
            ->limit(5)
            ->get();

        $warehousesStock = Warehouse::withSum('inventoryBalances as total_physical', 'qty_physical')
            ->withSum('inventoryBalances as total_available', 'qty_available')
            ->withCount('inventoryBalances')
            ->orderBy('name')
            ->get();

        $categoryDistribution = Category::whereHas('products', fn ($q) => $q->published())
            ->withCount(['products' => fn ($q) => $q->published()])
            ->orderByDesc('products_count')
            ->get();

        // Valorización total del inventario físico (stock_fisico * precio_producto)
        $inventoryValuation = DB::table('inventory_balances')
            ->join('products', 'inventory_balances.product_id', '=', 'products.id')
            ->selectRaw('SUM(inventory_balances.qty_physical * products.price) as total_value')
            ->value('total_value') ?? 0;

        $stats = [
            'total_users' => User::count(),
            'active_roles' => Role::count(),
            'recent_logs' => AuditLog::count(),
            'current_role' => $user->roles->first()?->display_name ?? 'Sin rol asignado',
            'total_products' => Product::count(),
            'published_products' => Product::where('status', 'published')->count(),
            'draft_products' => Product::where('status', 'draft')->count(),
            'archived_products' => Product::where('status', 'archived')->count(),
            'categories_count' => Category::count(),
            'brands_count' => Brand::count(),
            'total_stock_physical' => (int) InventoryBalance::sum('qty_physical'),
            'total_stock_available' => (int) InventoryBalance::sum('qty_available'),
            'total_stock_reserved' => (int) InventoryBalance::sum('qty_reserved'),
            'low_stock_count' => InventoryBalance::lowStock()->count(),
            'out_of_stock_count' => InventoryBalance::outOfStock()->count(),
            'warehouses_count' => Warehouse::count(),
            'active_reservations' => StockReservation::whereIn('status', ['pending', 'confirmed'])->count(),
            'total_movements' => StockMovement::count(),
            'inventory_valuation' => (float) $inventoryValuation,
        ];

        return view('admin.dashboard', compact(
            'user',
            'recentAudit',
            'recentMovements',
            'lowStockBalances',
            'warehousesStock',
            'categoryDistribution',
            'stats'
        ));
    }

    /**
     * Muestra la lista de auditoría del sistema (Protegido por permiso audit.view).
     */
    public function auditLogs(Request $request): View
    {
        $query = AuditLog::with('user')->latest('id');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->paginate(15);

        return view('admin.audit', compact('logs'));
    }

    /**
     * Muestra la lista de usuarios y roles del sistema (Protegido por roles superadmin y admin).
     */
    public function users(): View
    {
        $users = User::with('roles')->latest('id')->paginate(10);
        $roles = Role::all();

        return view('admin.users', compact('users', 'roles'));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']), 'status' => 'active',
        ]);
        $user->roles()->sync([$data['role_id']]);

        return back()->with('status', 'Usuario creado correctamente.');
    }

    public function resetUserPassword(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);
        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', 'Contraseña actualizada correctamente.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);
        $user->update(collect($data)->except('role_id')->all());
        $user->roles()->sync([$data['role_id']]);

        return back()->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 422, 'No puedes eliminar tu propio usuario.');
        $user->delete();

        return back()->with('status', 'Usuario eliminado correctamente.');
    }

    /**
     * Vista de perfil del usuario autenticado.
     */
    public function profile(): View
    {
        $user = auth()->user()->load('roles.permissions');

        return view('admin.profile', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar_url' => ['nullable', 'url', 'max:2048'],
        ]);
        auth()->user()->update($data);

        return back()->with('status', 'Perfil actualizado correctamente.');
    }
}
