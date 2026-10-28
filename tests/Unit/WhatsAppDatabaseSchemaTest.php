<?php

namespace Tests\Unit;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\WhatsappWebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppDatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversation_can_track_unread_messages_and_24h_window(): void
    {
        $conversation = Conversation::create([
            'customer_name' => 'Carlos Perez',
            'customer_phone' => '998877665',
            'status' => 'open',
            'unread_count' => 3,
            'last_incoming_at' => now()->subHours(2),
        ]);

        $this->assertSame(3, $conversation->unread_count);
        $this->assertTrue($conversation->isWithin24HourWindow());
        $this->assertSame('51998877665', $conversation->cleanPhone());

        // Mensaje entrante
        $message = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'direction' => 'inbound',
            'sender_type' => 'customer',
            'type' => 'text',
            'content' => 'Hola, quiero consultar el stock.',
            'whatsapp_message_id' => 'wamid.HBgLMTEyMjMzNDQ1NQ==',
            'status' => 'delivered',
            'sent_at' => now(),
        ]);

        $this->assertTrue($message->isInbound());
        $this->assertFalse($message->isOutbound());
        $this->assertTrue($message->isDelivered());
        $this->assertFalse($message->isRead());

        // Marcar como leído
        $conversation->markAsRead();
        $conversation->refresh();
        $message->refresh();

        $this->assertSame(0, $conversation->unread_count);
        $this->assertTrue($message->isRead());
        $this->assertNotNull($message->read_at);
    }

    public function test_conversation_recognizes_expired_24h_window(): void
    {
        $conversation = Conversation::create([
            'customer_name' => 'Cliente Inactivo',
            'customer_phone' => '912345678',
            'status' => 'open',
            'last_incoming_at' => now()->subHours(25),
        ]);

        $this->assertFalse($conversation->isWithin24HourWindow());
    }

    public function test_webhook_event_model_tracks_and_detects_processed_events(): void
    {
        $this->assertFalse(WhatsappWebhookEvent::hasEventBeenProcessed('EVT-1001'));

        $event = WhatsappWebhookEvent::create([
            'event_id' => 'EVT-1001',
            'message_id' => 'wamid.12345',
            'event_type' => 'messages',
            'payload' => ['entry' => []],
            'processed_at' => now(),
        ]);

        $this->assertTrue($event->isProcessed());
        $this->assertTrue(WhatsappWebhookEvent::hasEventBeenProcessed('EVT-1001'));
        $this->assertFalse(WhatsappWebhookEvent::hasEventBeenProcessed('EVT-9999'));
    }
}
