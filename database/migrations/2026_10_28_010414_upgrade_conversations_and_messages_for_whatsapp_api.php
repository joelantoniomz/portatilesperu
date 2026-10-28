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
        Schema::table('conversations', function (Blueprint $table) {
            $table->unsignedInteger('unread_count')->default(0)->after('status');
            $table->timestamp('last_incoming_at')->nullable()->after('last_activity_at');
        });

        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->string('direction', 20)->default('outbound')->after('user_id'); // inbound | outbound
            $table->string('sender_type', 30)->default('advisor')->after('direction'); // customer | advisor | system
            $table->string('type', 40)->default('text')->change();
            $table->string('whatsapp_message_id', 120)->nullable()->unique()->after('type');
            $table->string('status', 30)->default('sent')->after('whatsapp_message_id'); // pending | sent | delivered | read | failed
            $table->string('attachment_url', 500)->nullable()->after('content');
            $table->string('attachment_type', 40)->nullable()->after('attachment_url');
            $table->string('attachment_filename', 255)->nullable()->after('attachment_type');
            $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_filename');
            $table->string('error_code', 50)->nullable()->after('attachment_size');
            $table->text('error_message')->nullable()->after('error_code');
            $table->timestamp('delivered_at')->nullable()->after('sent_at');
            $table->timestamp('read_at')->nullable()->after('delivered_at');
            $table->json('raw_payload')->nullable()->after('read_at');
        });

        Schema::create('whatsapp_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 120)->nullable()->unique();
            $table->string('message_id', 120)->nullable()->index();
            $table->string('event_type', 60)->index();
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_webhook_events');

        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->dropUnique(['whatsapp_message_id']);
            $table->dropColumn([
                'direction',
                'sender_type',
                'whatsapp_message_id',
                'status',
                'attachment_url',
                'attachment_type',
                'attachment_filename',
                'attachment_size',
                'error_code',
                'error_message',
                'delivered_at',
                'read_at',
                'raw_payload',
            ]);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['unread_count', 'last_incoming_at']);
        });
    }
};
