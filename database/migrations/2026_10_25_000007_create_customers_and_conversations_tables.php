<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tablas para Fase 7: Clientes y bandeja de conversaciones WhatsApp (modo enlace).
     *
     * Diseño:
     *  - customers: perfil de cliente generado desde pedidos o registro manual.
     *  - customer_notes: notas internas del vendedor/admin sobre un cliente.
     *  - conversations: registro de interacciones WhatsApp por enlace (no API).
     */
    public function up(): void
    {
        // ── Clientes ─────────────────────────────────────────────────────────────
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('phone', 30)->nullable()->index();
            $table->string('email', 150)->nullable()->index();
            $table->string('city', 80)->nullable();
            $table->string('address', 300)->nullable();
            $table->enum('document_type', ['dni', 'ruc', 'ce', 'pasaporte'])->default('dni');
            $table->string('document_number', 20)->nullable();
            $table->string('company_name', 150)->nullable();   // Para facturas B2B
            $table->string('source', 40)->default('web');       // web | manual | import
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->text('internal_notes')->nullable();          // Notas rápidas del admin
            $table->timestamp('first_purchase_at')->nullable(); // Fecha primer pedido
            $table->timestamp('last_purchase_at')->nullable();  // Fecha último pedido
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->timestamps();
        });

        // ── Notas de vendedores sobre clientes ───────────────────────────────────
        Schema::create('customer_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->text('note');
            $table->string('type', 40)->default('general'); // general | followup | complaint | sale
            $table->timestamps();
        });

        // ── Conversaciones / Interacciones WhatsApp (modo enlace sin API) ────────
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Vendedor asignado
            $table->string('channel', 40)->default('whatsapp_link'); // whatsapp_link | whatsapp_api
            $table->string('customer_phone', 30)->nullable();
            $table->string('customer_name', 150)->nullable();
            $table->string('order_reference', 20)->nullable(); // PED-YYYY-XXXXX
            $table->enum('status', ['open', 'in_progress', 'resolved', 'archived'])->default('open');
            $table->text('summary')->nullable();          // Resumen escrito por vendedor
            $table->text('last_message_preview')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });

        // ── Mensajes internos de la conversación (notas, no mensajes reales de WA) ─
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['note', 'whatsapp_link', 'status_change'])->default('note');
            $table->text('content');
            $table->timestamp('sent_at')->useCurrent();
        });

        // ── FK: orders.customer_id (enlace con perfil de cliente) ────────────────
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->after('warehouse_id')
                ->constrained()
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeignIdFor('customers');
        });
        Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('customer_notes');
        Schema::dropIfExists('customers');
    }
};
