<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Categorías
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Subcategorías
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Marcas
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Modelos de Producto
        Schema::create('product_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['brand_id', 'slug']);
        });

        // 5. Productos
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('name', 200);
            $table->string('slug', 220)->unique();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('subcategories')->nullOnDelete();
            $table->foreignId('brand_id')->constrained('brands')->restrictOnDelete();
            $table->foreignId('product_model_id')->nullable()->constrained('product_models')->nullOnDelete();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('PEN');
            $table->string('barcode', 50)->nullable()->index();
            $table->string('qr_code')->nullable();
            $table->string('status', 20)->default('draft')->index(); // draft, published, archived
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->integer('min_stock_alert')->default(5);
            $table->decimal('weight', 8, 2)->nullable(); // Peso en kilogramos
            $table->string('dimensions', 100)->nullable(); // Ej: 35.8 x 24.2 x 1.99 cm
            $table->json('technical_specs')->nullable(); // Ficha técnica estructurada
            $table->timestamps();

            $table->index(['status', 'is_featured']);
            $table->index(['category_id', 'status']);
            $table->index(['brand_id', 'status']);
        });

        // 6. Galería de Imágenes de Producto (6–8 imágenes)
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('file_path');
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('alt_text', 200)->nullable();
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_models');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('subcategories');
        Schema::dropIfExists('categories');
    }
};
