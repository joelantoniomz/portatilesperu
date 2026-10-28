<?php

namespace Tests\Unit;

use App\Services\WhatsAppCloudApiService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppCloudApiServiceTest extends TestCase
{
    public function test_service_detects_unconfigured_mode_and_reports_missing_keys(): void
    {
        Config::set('services.whatsapp', [
            'enabled' => false,
            'phone_number_id' => null,
            'access_token' => null,
            'webhook_verify_token' => null,
        ]);

        $service = new WhatsAppCloudApiService;

        $this->assertFalse($service->isEnabled());
        $this->assertFalse($service->isConfigured());

        $status = $service->getConfigurationStatus();
        $this->assertSame('configuration_required', $status['mode']);
        $this->assertNotEmpty($status['missing_keys']);

        // No debe intentar enviar si no está configurado
        $result = $service->sendTextMessage('987654321', 'Hola mundo');
        $this->assertFalse($result['success']);
        $this->assertSame('NOT_CONFIGURED', $result['error_code']);
    }

    public function test_service_verifies_webhook_token_correctly(): void
    {
        Config::set('services.whatsapp.webhook_verify_token', 'mi_secreto_super_seguro_2026');
        $service = new WhatsAppCloudApiService;

        $this->assertTrue($service->verifyWebhookToken('subscribe', 'mi_secreto_super_seguro_2026'));
        $this->assertFalse($service->verifyWebhookToken('subscribe', 'token_invalido'));
        $this->assertFalse($service->verifyWebhookToken('other_mode', 'mi_secreto_super_seguro_2026'));
    }

    public function test_service_validates_hmac_signature_correctly(): void
    {
        Config::set('services.whatsapp.app_secret', 'secret123');
        $service = new WhatsAppCloudApiService;

        $payload = json_encode(['entry' => ['id' => '123']]);
        $validHash = hash_hmac('sha256', $payload, 'secret123');

        $this->assertTrue($service->validateWebhookSignature($payload, "sha256={$validHash}"));
        $this->assertFalse($service->validateWebhookSignature($payload, 'sha256=invalid_hash'));
        $this->assertFalse($service->validateWebhookSignature($payload, 'invalid_header_format'));
    }

    public function test_service_sends_text_message_successfully_via_http_fake(): void
    {
        Config::set('services.whatsapp', [
            'enabled' => true,
            'version' => 'v22.0',
            'phone_number_id' => '10987654321',
            'access_token' => 'EAAG_TEST_TOKEN',
            'webhook_verify_token' => 'token123',
            'base_url' => 'https://graph.facebook.com',
        ]);

        Http::fake([
            'https://graph.facebook.com/v22.0/10987654321/messages' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts' => [['input' => '51987654321', 'wa_id' => '51987654321']],
                'messages' => [['id' => 'wamid.HBgLMTEyMjMzNDQ1NQ==']],
            ], 200),
        ]);

        $service = new WhatsAppCloudApiService;
        $this->assertTrue($service->isConfigured());

        $result = $service->sendTextMessage('987654321', 'Hola desde Portátiles Perú');

        $this->assertTrue($result['success']);
        $this->assertSame('wamid.HBgLMTEyMjMzNDQ1NQ==', $result['message_id']);
        $this->assertNull($result['error']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://graph.facebook.com/v22.0/10987654321/messages'
                && $request['to'] === '51987654321'
                && $request['text']['body'] === 'Hola desde Portátiles Perú';
        });
    }

    public function test_service_sends_media_message_successfully(): void
    {
        Config::set('services.whatsapp', [
            'enabled' => true,
            'version' => 'v22.0',
            'phone_number_id' => '10987654321',
            'access_token' => 'EAAG_TEST_TOKEN',
        ]);

        Http::fake([
            'https://graph.facebook.com/v22.0/10987654321/messages' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.MEDIA_TEST_001']],
            ], 200),
        ]);

        $service = new WhatsAppCloudApiService;

        $result = $service->sendMediaMessage(
            toPhone: '51999888777',
            type: 'document',
            mediaUrl: 'https://portatiles-peru.test/storage/catalog.pdf',
            caption: 'Catálogo de Laptops Oficial',
            filename: 'Catalogo-Portatiles-Peru.pdf'
        );

        $this->assertTrue($result['success']);
        $this->assertSame('wamid.MEDIA_TEST_001', $result['message_id']);

        Http::assertSent(function ($request) {
            return $request['type'] === 'document'
                && $request['document']['link'] === 'https://portatiles-peru.test/storage/catalog.pdf'
                && $request['document']['caption'] === 'Catálogo de Laptops Oficial';
        });
    }

    public function test_service_handles_meta_api_errors_gracefully(): void
    {
        Config::set('services.whatsapp', [
            'enabled' => true,
            'version' => 'v22.0',
            'phone_number_id' => '10987654321',
            'access_token' => 'INVALID_TOKEN',
        ]);

        Http::fake([
            'https://graph.facebook.com/v22.0/10987654321/messages' => Http::response([
                'error' => [
                    'message' => 'Invalid OAuth access token - Cannot parse access token',
                    'type' => 'OAuthException',
                    'code' => 190,
                ],
            ], 401),
        ]);

        $service = new WhatsAppCloudApiService;
        $result = $service->sendTextMessage('987654321', 'Prueba error');

        $this->assertFalse($result['success']);
        $this->assertSame('190', $result['error_code']);
        $this->assertStringContainsString('Invalid OAuth access token', $result['error']);
    }

    public function test_service_formats_peruvian_phone_numbers(): void
    {
        $service = new WhatsAppCloudApiService;

        $this->assertSame('51987654321', $service->formatRecipientPhone('987654321'));
        $this->assertSame('51987654321', $service->formatRecipientPhone('51987654321'));
        $this->assertSame('51987654321', $service->formatRecipientPhone('+51 987 654 321'));
        $this->assertSame('14155552671', $service->formatRecipientPhone('+1 (415) 555-2671'));
    }
}
