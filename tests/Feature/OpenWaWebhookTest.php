<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Customer;
use App\Models\WhatsappSession;
use App\Models\WhatsappWebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class OpenWaWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected string $webhookSecret = 'test-secret-key-signature';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.openwa.base_url', 'https://openwa.test');
        Config::set('services.openwa.api_key', 'api-key-test');
        Config::set('services.openwa.session_id', 'default');
        Config::set('services.openwa.webhook_secret', $this->webhookSecret);
    }

    public function test_webhook_rejects_invalid_hmac_signature(): void
    {
        $payload = json_encode([
            'event' => 'message.received',
            'data' => [
                'id' => 'msg_112233',
                'from' => '51987654321@c.us',
                'body' => 'Hola con firma falsa',
            ],
        ]);

        $response = $this->withHeaders([
            'X-OpenWA-Signature' => 'sha256=invalid-signature',
        ])->postJson('/webhook/openwa', json_decode($payload, true));

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Invalid HMAC signature']);
    }

    public function test_webhook_receives_incoming_message_and_creates_records(): void
    {
        $data = [
            'event' => 'message.received',
            'id' => 'evt_1001',
            'data' => [
                'id' => 'true_51987654321@c.us_MSG999',
                'from' => '51987654321@c.us',
                'notifyName' => 'Juan Perez',
                'body' => 'Hola, busco cotización de laptop Lenovo',
                'type' => 'chat',
                'timestamp' => 1730000000,
            ],
        ];

        $payload = json_encode($data);
        $signature = hash_hmac('sha256', $payload, $this->webhookSecret);

        $response = $this->call(
            'POST',
            '/webhook/openwa',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_OPENWA_SIGNATURE' => 'sha256=' . $signature,
                'HTTP_X_OPENWA_IDEMPOTENCY_KEY' => 'idem_key_001',
            ],
            $payload
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verificar creación de cliente
        $this->assertDatabaseHas('customers', [
            'phone' => '51987654321',
            'name' => 'Juan Perez',
        ]);

        // Verificar conversación
        $this->assertDatabaseHas('conversations', [
            'customer_phone' => '51987654321',
            'channel' => 'whatsapp_openwa',
            'unread_count' => 1,
        ]);

        // Verificar mensaje guardado
        $this->assertDatabaseHas('conversation_messages', [
            'openwa_message_id' => 'true_51987654321@c.us_MSG999',
            'direction' => 'inbound',
            'content' => 'Hola, busco cotización de laptop Lenovo',
        ]);
    }

    public function test_webhook_prevents_duplicate_processing_via_idempotency_key(): void
    {
        $data = [
            'event' => 'message.received',
            'id' => 'evt_2002',
            'data' => [
                'id' => 'true_51987654321@c.us_MSG888',
                'from' => '51987654321@c.us',
                'notifyName' => 'Carlos Gomez',
                'body' => 'Mensaje repetido',
                'type' => 'chat',
            ],
        ];

        $payload = json_encode($data);
        $signature = hash_hmac('sha256', $payload, $this->webhookSecret);

        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_OPENWA_SIGNATURE' => 'sha256=' . $signature,
            'HTTP_X_OPENWA_IDEMPOTENCY_KEY' => 'idem_duplicate_test',
        ];

        // Primer envío
        $firstResponse = $this->call('POST', '/webhook/openwa', [], [], [], $headers, $payload);
        $firstResponse->assertStatus(200);

        // Segundo envío con la misma clave de idempotencia
        $secondResponse = $this->call('POST', '/webhook/openwa', [], [], [], $headers, $payload);
        $secondResponse->assertStatus(200);
        $secondResponse->assertJsonFragment(['message' => 'Event already processed (idempotent)']);

        // Solo debe haber 1 mensaje registrado
        $this->assertEquals(1, ConversationMessage::where('openwa_message_id', 'true_51987654321@c.us_MSG888')->count());
    }

    public function test_webhook_processes_session_status_update(): void
    {
        $data = [
            'event' => 'session.status',
            'data' => [
                'session' => 'default',
                'status' => 'WORKING',
                'phone' => '51900000000',
                'battery' => 92,
            ],
        ];

        $payload = json_encode($data);
        $signature = hash_hmac('sha256', $payload, $this->webhookSecret);

        $response = $this->call(
            'POST',
            '/webhook/openwa',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_OPENWA_SIGNATURE' => 'sha256=' . $signature,
            ],
            $payload
        );

        $response->assertStatus(200);

        $session = WhatsappSession::where('session_id', 'default')->first();
        $this->assertNotNull($session);
        $this->assertEquals('WORKING', $session->status);
        $this->assertTrue($session->is_connected);
        $this->assertEquals(92, $session->battery_level);
    }
}

