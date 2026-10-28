<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::with('subcategories')->withCount('products')->orderBy('sort_order')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        $category = Category::create($data);

        AuditLog::log('category_created', 'categories', $category->id, null, ['name' => $category->name]);

        return back()->with('status', "Categoría '{$category->name}' creada con éxito.");
    }

    public function storeSubcategory(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $data['category_id'] = $category->id;
        $data['slug'] = Str::slug($data['name'].'-'.$category->slug);
        $data['is_active'] = true;

        $subcategory = Subcategory::create($data);

        AuditLog::log('subcategory_created', 'subcategories', $subcategory->id, null, [
            'name' => $subcategory->name,
            'category_id' => $category->id,
        ]);

        return back()->with('status', "Subcategoría '{$subcategory->name}' agregada.");
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $category->update($data);

        AuditLog::log('category_updated', 'categories', $category->id, null, ['name' => $category->name]);

        return back()->with('status', "Categoría '{$category->name}' actualizada.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->count() > 0) {
            return back()->withErrors(['error' => "No se puede eliminar la categoría '{$category->name}' porque contiene productos asociados."]);
        }

        $name = $category->name;
        $category->subcategories()->delete();
        $category->delete();

        AuditLog::log('category_deleted', 'categories', null, null, ['name' => $name]);

        return back()->with('status', "Categoría '{$name}' eliminada con éxito.");
    }

    public function destroySubcategory(Category $category, Subcategory $subcategory): RedirectResponse
    {
        if ($subcategory->category_id !== $category->id) {
            abort(404);
        }

        $name = $subcategory->name;
        $subcategory->delete();

        AuditLog::log('subcategory_deleted', 'subcategories', null, null, [
            'name' => $name,
            'category_id' => $category->id,
        ]);

        return back()->with('status', "Subcategoría '{$name}' eliminada correctamente.");
    }
}
