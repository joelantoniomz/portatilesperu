<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenWaService
{
    protected string $baseUrl;

    protected ?string $apiKey;

    protected string $sessionId;

    protected ?string $webhookSecret;

    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) (Setting::getValue('openwa_base_url', config('services.openwa.base_url', 'http://localhost:2785'))), '/');
        $this->apiKey = Setting::getValue('openwa_api_key', config('services.openwa.api_key'));
        $this->sessionId = Setting::getValue('openwa_session_id', config('services.openwa.session_id', 'default'));
        $this->webhookSecret = Setting::getValue('openwa_webhook_secret', config('services.openwa.webhook_secret'));
        $this->timeout = (int) (Setting::getValue('openwa_timeout', config('services.openwa.timeout', 15)));
    }

    /**
     * Determina si OpenWA cuenta con las credenciales mínimas configuradas.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->baseUrl) && ! empty($this->apiKey);
    }

    /**
     * Retorna el identificador de sesión activo.
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * Retorna la URL base configurada para OpenWA.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Retorna el secreto para validación HMAC de webhooks.
     */
    public function getWebhookSecret(): ?string
    {
        return $this->webhookSecret;
    }

    /**
     * Cliente HTTP base configurado con cabeceras de autenticación y timeout.
     */
    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'X-API-Key' => $this->apiKey,
                'api_key' => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->timeout($this->timeout);
    }

    /**
     * Obtiene el estado de conexión de la sesión de WhatsApp en OpenWA.
     */
    public function getSessionStatus(): array
    {
        if (! $this->isConfigured()) {
            return [
                'connected' => false,
                'status' => 'not_configured',
                'message' => 'Servicio OpenWA no configurado. Se requieren URL y API Key.',
            ];
        }

        try {
            $response = $this->client()->get("/api/sessions/{$this->sessionId}/status");

            if (! $response->successful()) {
                // Intentar fallback al endpoint directo de sesión si la versión de API varía
                $fallback = $this->client()->get("/api/sessions/{$this->sessionId}");
                if ($fallback->successful()) {
                    $response = $fallback;
                }
            }

            if ($response->successful()) {
                $data = $response->json() ?? [];
                $statusString = strtoupper((string) ($data['status'] ?? $data['state'] ?? 'UNKNOWN'));
                $isConnected = in_array($statusString, ['WORKING', 'CONNECTED', 'READY', 'OPEN', 'AUTHENTICATED']);

                return [
                    'connected' => $isConnected,
                    'status' => strtolower($statusString),
                    'raw_status' => $statusString,
                    'qr_code' => $data['qrcode'] ?? $data['qr'] ?? null,
                    'battery' => $data['battery'] ?? null,
                    'phone' => $data['phone'] ?? $data['me'] ?? null,
                    'data' => $data,
                ];
            }

            return [
                'connected' => false,
                'status' => 'offline',
                'http_code' => $response->status(),
                'error' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::warning('Error consultando estado de OpenWA: ' . $e->getMessage());

            return [
                'connected' => false,
                'status' => 'unreachable',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Genera o reinicia la sesión solicitando código QR para vinculación.
     */
    public function startSession(): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'error' => 'OpenWA no configurado'];
        }

        try {
            $response = $this->client()->post("/api/sessions/{$this->sessionId}/start", [
                'waitQrCode' => true,
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Error iniciando sesión en OpenWA: ' . $e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envía un mensaje de texto plano a través de OpenWA.
     */
    public function sendText(string $phone, string $message): array
    {
        $phoneFormatted = $this->formatPhone($phone);
        $chatId = $this->formatChatId($phoneFormatted);

        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'status' => 'not_configured',
                'error' => 'OpenWA no está configurado. Por favor configura OPENWA_BASE_URL y OPENWA_API_KEY.',
            ];
        }

        try {
            $payload = [
                'chatId' => $chatId,
                'phone' => $phoneFormatted,
                'text' => $message,
                'message' => $message,
            ];

            $response = $this->client()->post("/api/sessions/{$this->sessionId}/messages/send-text", $payload);

            if ($response->successful()) {
                $responseData = $response->json() ?? [];
                $messageId = $responseData['id'] ?? $responseData['messageId'] ?? $responseData['response']['id'] ?? null;

                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'status' => 'sent',
                    'data' => $responseData,
                ];
            }

            Log::error('OpenWA sendText falló HTTP ' . $response->status() . ': ' . $response->body());

            return [
                'success' => false,
                'status' => 'failed',
                'http_code' => $response->status(),
                'error' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('OpenWA sendText Exception: ' . $e->getMessage());

            return [
                'success' => false,
                'status' => 'exception',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envía un archivo multimedia o documento a través de OpenWA.
     */
    public function sendFile(string $phone, string $filePath, string $filename, ?string $caption = null): array
    {
        $chatId = $this->formatChatId($phone);

        if (! $this->isConfigured()) {
            return ['success' => false, 'error' => 'OpenWA no configurado'];
        }

        try {
            $request = $this->client()->attach('file', file_get_contents($filePath), $filename);

            $response = $request->post("/api/sessions/{$this->sessionId}/messages/send-file", [
                'chatId' => $chatId,
                'caption' => $caption ?? '',
                'filename' => $filename,
            ]);

            if ($response->successful()) {
                $data = $response->json() ?? [];
                return [
                    'success' => true,
                    'message_id' => $data['id'] ?? $data['messageId'] ?? null,
                    'data' => $data,
                ];
            }

            return [
                'success' => false,
                'http_code' => $response->status(),
                'error' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('OpenWA sendFile Exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Valida la firma criptográfica HMAC-SHA256 enviada por el Webhook de OpenWA.
     *
     * @param string $rawContent Cuerpo crudo exacto de la petición HTTP recibida
     * @param string|null $signature Firma contenida en cabecera X-OpenWA-Signature
     */
    public function verifyWebhookSignature(string $rawContent, ?string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            // Si no se configuró webhook secret, registrar advertencia y requerir secret para ambiente seguro
            Log::warning('OpenWA webhook secret no está configurado. Recomendado configurar OPENWA_WEBHOOK_SECRET.');
            return false;
        }

        if (empty($signature)) {
            return false;
        }

        // Si la firma viene en formato 'sha256=xxx' o directamente el hash hex
        $providedHash = str_starts_with($signature, 'sha256=') ? substr($signature, 7) : $signature;
        $expectedHash = hash_hmac('sha256', $rawContent, $this->webhookSecret);

        return hash_equals($expectedHash, $providedHash);
    }

    /**
     * Normaliza un número telefónico a formato internacional sin espacios ni signos +.
     */
    public function formatPhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Si tiene 9 dígitos y empieza en 9 (celular peruano estándar), anteponer prefijo de país 51
        if (strlen($cleaned) === 9 && str_starts_with($cleaned, '9')) {
            return '51' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Genera el chatId con formato @c.us esperado por WhatsApp y OpenWA.
     */
    public function formatChatId(string $phone): string
    {
        $normalized = $this->formatPhone($phone);
        if (! str_ends_with($normalized, '@c.us')) {
            return $normalized . '@c.us';
        }

        return $normalized;
    }
}
