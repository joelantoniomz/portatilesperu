<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('catalog.manage');
    }

    public function rules(): array
    {
        $productId = $this->route('product') instanceof Product
            ? $this->route('product')->id
            : $this->route('product');

        return [
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($productId)],
            'name' => ['required', 'string', 'max:200'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'in:PEN,USD'],
            'category_id' => ['required', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'exists:subcategories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'product_model_id' => ['nullable', 'exists:product_models,id'],
            'status' => ['required', 'in:draft,published,archived'],
            'is_featured' => ['nullable', 'boolean'],
            'is_new' => ['nullable', 'boolean'],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('products', 'barcode')->ignore($productId)],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'dimensions' => ['nullable', 'string', 'max:100'],
            'min_stock_alert' => ['nullable', 'integer', 'min:0'],
            'technical_specs' => ['nullable', 'array'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:8192'],
            'image_urls' => ['nullable', 'array'],
            'image_urls.*' => ['nullable', 'string', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required' => 'El código SKU es obligatorio.',
            'sku.unique' => 'El SKU ingresado ya se encuentra registrado por otro producto.',
            'name.required' => 'El nombre del producto es obligatorio.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un valor numérico.',
            'price.min' => 'El precio no puede ser negativo.',
            'category_id.required' => 'Debe seleccionar una categoría.',
            'brand_id.required' => 'Debe seleccionar una marca.',
            'status.in' => 'El estado debe ser Borrador, Publicado o Archivado.',
            'images.max' => 'Puede subir un máximo de 8 imágenes por producto.',
            'images.*.image' => 'Los archivos deben ser imágenes válidas.',
            'images.*.mimes' => 'Formatos de imagen permitidos: JPG, PNG, WEBP.',
            'images.*.max' => 'Cada imagen no puede superar los 4 MB de tamaño.',
            'barcode.unique' => 'El código EAN ya pertenece a otro producto.',
            'image_urls.max' => 'Puede registrar hasta 3 URLs de imágenes.',
        ];
    }
}
