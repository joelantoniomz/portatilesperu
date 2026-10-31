<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for OpenWA sessions and WhatsApp messaging enhancements.
     */
    public function up(): void
    {
        // 1. Tabla de sesiones de WhatsApp (OpenWA / WPPConnect)
        if (! Schema::hasTable('whatsapp_sessions')) {
            Schema::create('whatsapp_sessions', function (Blueprint $table) {
                $table->id();
                $table->string('session_id', 60)->unique();
                $table->string('status', 40)->default('STOPPED'); // WORKING, SCAN_QR_CODE, STOPPED, FAILED
                $table->string('phone_number', 40)->nullable();
                $table->text('qr_code')->nullable();
                $table->unsignedTinyInteger('battery_level')->nullable();
                $table->boolean('is_connected')->default(false);
                $table->json('connection_data')->nullable();
                $table->timestamp('last_active_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. Extender tabla de conversaciones si falta campos
        Schema::table('conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('conversations', 'session_id')) {
                $table->string('session_id', 60)->default('default')->after('order_reference')->index();
            }
        });

        // 3. Extender tabla de mensajes de conversación
        Schema::table('conversation_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('conversation_messages', 'openwa_message_id')) {
                $table->string('openwa_message_id', 150)->nullable()->unique()->after('whatsapp_message_id');
            }
            if (! Schema::hasColumn('conversation_messages', 'sender_phone')) {
                $table->string('sender_phone', 40)->nullable()->after('sender_type')->index();
            }
            if (! Schema::hasColumn('conversation_messages', 'recipient_phone')) {
                $table->string('recipient_phone', 40)->nullable()->after('sender_phone')->index();
            }
        });

        // 4. Asegurar campos en whatsapp_webhook_events para idempotencia OpenWA
        if (Schema::hasTable('whatsapp_webhook_events')) {
            Schema::table('whatsapp_webhook_events', function (Blueprint $table) {
                if (! Schema::hasColumn('whatsapp_webhook_events', 'idempotency_key')) {
                    $table->string('idempotency_key', 120)->nullable()->unique()->after('event_id');
                }
                if (! Schema::hasColumn('whatsapp_webhook_events', 'source')) {
                    $table->string('source', 30)->default('openwa')->after('event_type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('whatsapp_webhook_events')) {
            Schema::table('whatsapp_webhook_events', function (Blueprint $table) {
                if (Schema::hasColumn('whatsapp_webhook_events', 'idempotency_key')) {
                    $table->dropUnique(['idempotency_key']);
                    $table->dropColumn(['idempotency_key', 'source']);
                }
            });
        }

        Schema::table('conversation_messages', function (Blueprint $table) {
            if (Schema::hasColumn('conversation_messages', 'openwa_message_id')) {
                $table->dropUnique(['openwa_message_id']);
                $table->dropColumn(['openwa_message_id', 'sender_phone', 'recipient_phone']);
            }
        });

        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'session_id')) {
                $table->dropColumn('session_id');
            }
        });

        Schema::dropIfExists('whatsapp_sessions');
    }
};

