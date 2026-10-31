<?php

namespace Tests\Unit;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\WhatsappSession;
use App\Models\WhatsappWebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppOpenWaDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_whatsapp_session_creation_and_attributes(): void
    {
        $session = WhatsappSession::create([
            'session_id' => 'default',
            'status' => 'WORKING',
            'phone_number' => '51987654321',
            'battery_level' => 88,
            'is_connected' => true,
        ]);

        $this->assertDatabaseHas('whatsapp_sessions', [
            'session_id' => 'default',
            'is_connected' => true,
            'battery_level' => 88,
        ]);

        $retrieved = WhatsappSession::getSession('default');
        $this->assertEquals('WORKING', $retrieved->status);
    }

    public function test_conversation_and_message_openwa_fields(): void
    {
        $conversation = Conversation::create([
            'channel' => 'whatsapp_openwa',
            'session_id' => 'default',
            'customer_phone' => '51987654321',
            'customer_name' => 'Cliente Prueba',
            'status' => 'open',
        ]);

        $message = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'direction' => 'inbound',
            'sender_type' => 'customer',
            'sender_phone' => '51987654321',
            'recipient_phone' => '51900000000',
            'openwa_message_id' => 'true_51987654321@c.us_3EB0C123',
            'type' => 'text',
            'content' => 'Mensaje de prueba OpenWA',
            'status' => 'delivered',
            'sent_at' => now(),
        ]);

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'session_id' => 'default',
        ]);

        $this->assertDatabaseHas('conversation_messages', [
            'id' => $message->id,
            'openwa_message_id' => 'true_51987654321@c.us_3EB0C123',
            'sender_phone' => '51987654321',
        ]);
    }

    public function test_webhook_event_idempotency_storage(): void
    {
        $event = WhatsappWebhookEvent::create([
            'event_id' => 'evt_998877',
            'idempotency_key' => 'idem_key_12345',
            'event_type' => 'message.received',
            'source' => 'openwa',
            'payload' => ['body' => 'Hola'],
            'created_at' => now(),
        ]);

        $this->assertDatabaseHas('whatsapp_webhook_events', [
            'idempotency_key' => 'idem_key_12345',
            'source' => 'openwa',
        ]);
    }
}

