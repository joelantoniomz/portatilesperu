<?php

namespace Tests\Unit;

use App\Services\OpenWaService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenWaServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.openwa.base_url', 'https://openwa.test');
        Config::set('services.openwa.api_key', 'test-secret-key-123');
        Config::set('services.openwa.session_id', 'portatiles');
        Config::set('services.openwa.webhook_secret', 'my-hmac-webhook-secret');
        Config::set('services.openwa.timeout', 10);
    }

    public function test_service_initialization_and_configuration(): void
    {
        $service = new OpenWaService();

        $this->assertTrue($service->isConfigured());
        $this->assertEquals('portatiles', $service->getSessionId());
        $this->assertEquals('https://openwa.test', $service->getBaseUrl());
    }

    public function test_phone_formatting_and_chat_id(): void
    {
        $service = new OpenWaService();

        // Número peruano estándar de 9 dígitos
        $this->assertEquals('51987654321', $service->formatPhone('987654321'));
        $this->assertEquals('51987654321@c.us', $service->formatChatId('987654321'));

        // Número con código internacional y caracteres
        $this->assertEquals('51987654321', $service->formatPhone('+51 987 654 321'));
        $this->assertEquals('51987654321@c.us', $service->formatChatId('+51 987 654 321'));
    }

    public function test_verify_webhook_signature_success_and_failure(): void
    {
        $service = new OpenWaService();
        $payload = '{"event":"message.received","data":{"body":"Hola"}}';
        $secret = 'my-hmac-webhook-secret';

        $validHash = hash_hmac('sha256', $payload, $secret);

        // Con prefijo sha256=
        $this->assertTrue($service->verifyWebhookSignature($payload, 'sha256=' . $validHash));
        // Directo
        $this->assertTrue($service->verifyWebhookSignature($payload, $validHash));

        // Firma inválida o cuerpo alterado
        $this->assertFalse($service->verifyWebhookSignature($payload . 'tampered', $validHash));
        $this->assertFalse($service->verifyWebhookSignature($payload, 'invalid-signature'));
        $this->assertFalse($service->verifyWebhookSignature($payload, null));
    }

    public function test_get_session_status(): void
    {
        Http::fake([
            'https://openwa.test/api/sessions/portatiles/status' => Http::response([
                'status' => 'WORKING',
                'phone' => '51987654321',
                'battery' => 95,
            ], 200),
        ]);

        $service = new OpenWaService();
        $status = $service->getSessionStatus();

        $this->assertTrue($status['connected']);
        $this->assertEquals('working', $status['status']);
        $this->assertEquals('51987654321', $status['phone']);
    }

    public function test_send_text_message(): void
    {
        Http::fake([
            'https://openwa.test/api/sessions/portatiles/messages/send-text' => Http::response([
                'id' => 'true_51987654321@c.us_3EB0C12345',
                'status' => 'OK',
            ], 200),
        ]);

        $service = new OpenWaService();
        $result = $service->sendText('987654321', 'Hola desde Portátiles Perú');

        $this->assertTrue($result['success']);
        $this->assertEquals('true_51987654321@c.us_3EB0C12345', $result['message_id']);
        $this->assertEquals('sent', $result['status']);
    }
}

