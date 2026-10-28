<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudApiService
{
    protected bool $enabled;

    protected string $version;

    protected ?string $phoneNumberId;

    protected ?string $businessAccountId;

    protected ?string $accessToken;

    protected ?string $webhookVerifyToken;

    protected ?string $appSecret;

    protected string $baseUrl;

    public function __construct()
    {
        $config = config('services.whatsapp', []);

        $this->enabled = (bool) ($config['enabled'] ?? false);
        $this->version = (string) ($config['version'] ?? 'v22.0');
        $this->phoneNumberId = ! empty($config['phone_number_id']) ? (string) $config['phone_number_id'] : null;
        $this->businessAccountId = ! empty($config['business_account_id']) ? (string) $config['business_account_id'] : null;
        $this->accessToken = ! empty($config['access_token']) ? (string) $config['access_token'] : null;
        $this->webhookVerifyToken = ! empty($config['webhook_verify_token']) ? (string) $config['webhook_verify_token'] : null;
        $this->appSecret = ! empty($config['app_secret']) ? (string) $config['app_secret'] : null;
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? 'https://graph.facebook.com'), '/');
    }

    /**
     * Determina si el servicio está habilitado por variable de entorno.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Determina si cuenta con las credenciales mínimas indispensables para operar.
     */
    public function isConfigured(): bool
    {
        return $this->enabled
            && ! empty($this->phoneNumberId)
            && ! empty($this->accessToken);
    }

    /**
     * Reporta el estado de configuración y las variables pendientes.
     *
     * @return array{
     *     enabled: bool,
     *     configured: bool,
     *     phone_number_id: ?string,
     *     webhook_url: string,
     *     missing_keys: array<int, string>,
     *     mode: string
     * }
     */
    public function getConfigurationStatus(): array
    {
        $missing = [];

        if (! $this->enabled) {
            $missing[] = 'WHATSAPP_CLOUD_API_ENABLED (debe ser true)';
        }
        if (empty($this->phoneNumberId)) {
            $missing[] = 'WHATSAPP_PHONE_NUMBER_ID';
        }
        if (empty($this->accessToken)) {
            $missing[] = 'WHATSAPP_ACCESS_TOKEN';
        }
        if (empty($this->webhookVerifyToken)) {
            $missing[] = 'WHATSAPP_WEBHOOK_VERIFY_TOKEN';
        }

        $isConfigured = empty($missing);

        return [
            'enabled' => $this->enabled,
            'configured' => $isConfigured,
            'phone_number_id' => $this->phoneNumberId ? substr($this->phoneNumberId, 0, 4).'****'.substr($this->phoneNumberId, -3) : null,
            'webhook_url' => url('/webhook/whatsapp'),
            'missing_keys' => $missing,
            'mode' => $isConfigured ? 'cloud_api' : 'configuration_required',
        ];
    }

    /**
     * Valida el token de verificación de suscripción al webhook de Meta.
     */
    public function verifyWebhookToken(?string $mode, ?string $token): bool
    {
        if ($mode !== 'subscribe') {
            return false;
        }

        if (empty($this->webhookVerifyToken) || empty($token)) {
            return false;
        }

        return hash_equals($this->webhookVerifyToken, $token);
    }

    /**
     * Valida la firma criptográfica HMAC-SHA256 del webhook entrante.
     */
    public function validateWebhookSignature(string $rawPayload, ?string $signatureHeader): bool
    {
        if (empty($this->appSecret)) {
            // Si no se configuró secret, no bloqueamos la recepción pero advertimos en log.
            return true;
        }

        if (empty($signatureHeader) || ! str_starts_with($signatureHeader, 'sha256=')) {
            return false;
        }

        $signature = substr($signatureHeader, 7);
        $expectedSignature = hash_hmac('sha256', $rawPayload, $this->appSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Envía un mensaje de texto plano a un número de WhatsApp.
     *
     * @return array{
     *     success: bool,
     *     message_id: ?string,
     *     error: ?string,
     *     error_code: ?string,
     *     raw: ?array
     * }
     */
    public function sendTextMessage(string $toPhone, string $message, ?string $replyToMessageId = null): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message_id' => null,
                'error' => 'WhatsApp Cloud API no está configurada o se encuentra en modo configuración.',
                'error_code' => 'NOT_CONFIGURED',
                'raw' => null,
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatRecipientPhone($toPhone),
            'type' => 'text',
            'text' => [
                'preview_url' => true,
                'body' => $message,
            ],
        ];

        if ($replyToMessageId) {
            $payload['context'] = ['message_id' => $replyToMessageId];
        }

        return $this->sendHttpRequest($payload);
    }

    /**
     * Envía un archivo o documento (imagen, PDF de catálogo o ficha técnica) por WhatsApp.
     *
     * @param  string  $type  'image' | 'document' | 'audio' | 'video'
     * @return array{
     *     success: bool,
     *     message_id: ?string,
     *     error: ?string,
     *     error_code: ?string,
     *     raw: ?array
     * }
     */
    public function sendMediaMessage(string $toPhone, string $type, string $mediaUrl, ?string $caption = null, ?string $filename = null): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message_id' => null,
                'error' => 'WhatsApp Cloud API no está configurada o se encuentra en modo configuración.',
                'error_code' => 'NOT_CONFIGURED',
                'raw' => null,
            ];
        }

        $validTypes = ['image', 'document', 'audio', 'video'];
        if (! in_array($type, $validTypes, true)) {
            $type = 'document';
        }

        $mediaPayload = ['link' => $mediaUrl];

        if ($caption && in_array($type, ['image', 'document', 'video'], true)) {
            $mediaPayload['caption'] = $caption;
        }

        if ($filename && $type === 'document') {
            $mediaPayload['filename'] = $filename;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatRecipientPhone($toPhone),
            'type' => $type,
            $type => $mediaPayload,
        ];

        return $this->sendHttpRequest($payload);
    }

    /**
     * Envía un mensaje mediante plantilla aprobada (para iniciar conversación o contactar fuera de la ventana de 24h).
     *
     * @param  array<int, mixed>  $components
     * @return array{
     *     success: bool,
     *     message_id: ?string,
     *     error: ?string,
     *     error_code: ?string,
     *     raw: ?array
     * }
     */
    public function sendTemplateMessage(string $toPhone, string $templateName, string $languageCode = 'es', array $components = []): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message_id' => null,
                'error' => 'WhatsApp Cloud API no está configurada o se encuentra en modo configuración.',
                'error_code' => 'NOT_CONFIGURED',
                'raw' => null,
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatRecipientPhone($toPhone),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
            ],
        ];

        if (! empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $this->sendHttpRequest($payload);
    }

    /**
     * Marca un mensaje entrante como leído en la plataforma de WhatsApp.
     *
     * @return array{success: bool, error: ?string}
     */
    public function markMessageAsRead(string $messageId): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'error' => 'API no configurada'];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId,
        ];

        $response = $this->sendHttpRequest($payload);

        return ['success' => $response['success'], 'error' => $response['error']];
    }

    /**
     * Ejecuta la petición POST a la Graph API de Meta.
     *
     * @param  array<string, mixed>  $payload
     * @return array{
     *     success: bool,
     *     message_id: ?string,
     *     error: ?string,
     *     error_code: ?string,
     *     raw: ?array
     * }
     */
    protected function sendHttpRequest(array $payload): array
    {
        $url = "{$this->baseUrl}/{$this->version}/{$this->phoneNumberId}/messages";

        try {
            /** @var Response $response */
            $response = Http::withToken((string) $this->accessToken)
                ->acceptJson()
                ->timeout(15)
                ->post($url, $payload);

            $data = $response->json();

            if ($response->successful() && ! empty($data['messages'][0]['id'])) {
                return [
                    'success' => true,
                    'message_id' => (string) $data['messages'][0]['id'],
                    'error' => null,
                    'error_code' => null,
                    'raw' => $data,
                ];
            }

            // Caso de error retornado por Meta
            $errorMessage = $data['error']['message'] ?? 'Error desconocido al comunicar con WhatsApp API';
            $errorCode = isset($data['error']['code']) ? (string) $data['error']['code'] : 'API_ERROR';

            Log::error('Fallo en envío de WhatsApp Cloud API', [
                'status' => $response->status(),
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $errorMessage,
                'error_code' => $errorCode,
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('Excepción al conectar con WhatsApp Cloud API', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => 'No fue posible conectar con el servidor de WhatsApp: '.$e->getMessage(),
                'error_code' => 'CONNECTION_EXCEPTION',
                'raw' => null,
            ];
        }
    }

    /**
     * Limpia y formatea el número de teléfono con código de país.
     */
    public function formatRecipientPhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        // Si es número peruano de 9 dígitos sin prefijo internacional (51)
        if (! str_starts_with($clean, '51') && strlen($clean) === 9) {
            $clean = '51'.$clean;
        }

        return $clean;
    }
}
