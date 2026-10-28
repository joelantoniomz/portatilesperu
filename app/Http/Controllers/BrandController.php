<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\ProductModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::with('models')->withCount('products')->orderBy('name')->get();

        return view('admin.brands.index', compact('brands'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:brands,name'],
            'logo_url' => ['nullable', 'url', 'max:255'],
        ], [
            'name.required' => 'El nombre de la marca es obligatorio.',
            'name.unique' => 'Esta marca ya se encuentra registrada.',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = true;

        $brand = Brand::create($data);

        AuditLog::log('brand_created', 'brands', $brand->id, null, ['name' => $brand->name]);

        return back()->with('status', "Marca '{$brand->name}' registrada con éxito.");
    }

    public function storeModel(Request $request, Brand $brand): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $data['brand_id'] = $brand->id;
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = true;

        $model = ProductModel::create($data);

        AuditLog::log('product_model_created', 'product_models', $model->id, null, [
            'name' => $model->name,
            'brand_id' => $brand->id,
        ]);

        return back()->with('status', "Modelo '{$model->name}' asignado a {$brand->name}.");
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->products()->count() > 0) {
            return back()->withErrors(['error' => "No se puede eliminar la marca '{$brand->name}' porque tiene productos asociados."]);
        }

        $name = $brand->name;
        $brand->models()->delete();
        $brand->delete();

        AuditLog::log('brand_deleted', 'brands', null, null, ['name' => $name]);

        return back()->with('status', "Marca '{$name}' eliminada con éxito.");
    }

    public function destroyModel(Brand $brand, ProductModel $model): RedirectResponse
    {
        if ($model->brand_id !== $brand->id) {
            abort(404);
        }

        if ($model->products()->count() > 0) {
            return back()->withErrors(['error' => "No se puede eliminar el modelo '{$model->name}' porque tiene productos asociados."]);
        }

        $name = $model->name;
        $model->delete();

        AuditLog::log('product_model_deleted', 'product_models', null, null, [
            'name' => $name,
            'brand_id' => $brand->id,
        ]);

        return back()->with('status', "Modelo '{$name}' eliminado correctamente.");
    }
}
