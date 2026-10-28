<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('catalog.manage');
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
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
            'barcode' => ['nullable', 'string', 'max:50', 'unique:products,barcode'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'dimensions' => ['nullable', 'string', 'max:100'],
            'min_stock_alert' => ['nullable', 'integer', 'min:0'],
            'technical_specs' => ['nullable', 'array'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'image_urls' => ['nullable', 'array', 'max:8'],
            'image_urls.*' => ['nullable', 'url', 'max:2048'],
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
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'brand_id.required' => 'Debe seleccionar una marca.',
            'brand_id.exists' => 'La marca seleccionada no existe.',
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
