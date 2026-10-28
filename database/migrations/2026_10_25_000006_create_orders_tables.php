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
        // 1. Tabla de Pedidos / Solicitudes de Compra
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->string('customer_name', 120);
            $table->string('customer_phone', 30);
            $table->string('customer_email', 120)->nullable();
            $table->string('customer_city', 80)->nullable()->default('Lima');
            $table->text('customer_address')->nullable();
            $table->enum('document_type', ['dni', 'ruc', 'ce'])->nullable()->default('dni');
            $table->string('document_number', 20)->nullable();
            $table->enum('invoice_type', ['boleta', 'factura', 'nota_venta'])->default('boleta');
            $table->enum('status', ['pending', 'confirmed', 'in_preparation', 'dispatched', 'completed', 'cancelled'])->default('pending');
            $table->string('payment_method', 40)->nullable()->default('whatsapp_coordination');
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 3)->default('PEN');
            $table->text('notes')->nullable();
            $table->text('whatsapp_message')->nullable();
            $table->timestamp('whatsapp_sent_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->timestamps();

            $table->index('order_number');
            $table->index('status');
            $table->index('created_at');
        });

        // 2. Tabla de Items del Pedido
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->string('product_name', 200);
            $table->string('product_sku', 50);
            $table->decimal('unit_price', 12, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->foreignId('reservation_id')->nullable()->constrained('stock_reservations')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Historial de Cambios de Estado del Pedido
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('status_from', 30)->nullable();
            $table->string('status_to', 30);
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
