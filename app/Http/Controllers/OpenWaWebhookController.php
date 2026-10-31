<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\WhatsappSession;
use App\Models\WhatsappWebhookEvent;
use App\Services\OpenWaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OpenWaWebhookController extends Controller
{
    public function __construct(
        protected OpenWaService $openWaService
    ) {}

    /**
     * Endpoint POST para recibir eventos de OpenWA mediante Webhooks seguros.
     */
    public function handleIncoming(Request $request): JsonResponse
    {
        $rawContent = $request->getContent();
        $signature = $request->header('X-OpenWA-Signature') ?? $request->header('x-openwa-signature');
        $idempotencyKey = $request->header('X-OpenWA-Idempotency-Key') ?? $request->header('x-openwa-idempotency-key');

        // 1. Validar la firma HMAC SHA-256 sobre el cuerpo original
        $webhookSecret = $this->openWaService->getWebhookSecret();
        if (! empty($webhookSecret)) {
            if (! $this->openWaService->verifyWebhookSignature($rawContent, $signature)) {
                Log::warning('OpenWA Webhook: Firma HMAC inválida', [
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Invalid HMAC signature',
                ], 401);
            }
        }

        // Decodificar el payload JSON
        $payload = json_decode($rawContent, true);
        if (! is_array($payload)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid JSON payload',
            ], 400);
        }

        $eventType = $payload['event'] ?? $payload['type'] ?? $payload['event_type'] ?? 'unknown';
        $eventId = $payload['id'] ?? $payload['event_id'] ?? null;
        $data = $payload['data'] ?? $payload['payload'] ?? $payload;

        // 2. Control de Idempotencia estricto (X-OpenWA-Idempotency-Key o event_id)
        $uniqueKey = $idempotencyKey ?: ($eventId ? 'evt_' . $eventId : null);
        if ($uniqueKey) {
            $existing = WhatsappWebhookEvent::where('idempotency_key', $uniqueKey)->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event already processed (idempotent)',
                    'idempotency_key' => $uniqueKey,
                ], 200);
            }
        }

        // Registrar evento técnico
        $webhookEvent = WhatsappWebhookEvent::create([
            'event_id' => $eventId,
            'idempotency_key' => $uniqueKey,
            'event_type' => $eventType,
            'source' => 'openwa',
            'payload' => $payload,
            'created_at' => now(),
        ]);

        try {
            DB::beginTransaction();

            switch ($eventType) {
                case 'message.received':
                case 'message':
                case 'onMessage':
                    $this->processIncomingMessage($data);
                    break;

                case 'message.sent':
                case 'onAnyMessage':
                    if (isset($data['fromMe']) && $data['fromMe']) {
                        $this->processSentMessage($data);
                    } else {
                        $this->processIncomingMessage($data);
                    }
                    break;

                case 'message.ack':
                case 'onAck':
                    $this->processAckStatus($data);
                    break;

                case 'session.status':
                case 'session':
                case 'stateChange':
                    $this->processSessionStatus($data);
                    break;

                default:
                    Log::info('OpenWA Webhook evento no manejado: ' . $eventType);
                    break;
            }

            $webhookEvent->update(['processed_at' => now()]);
            DB::commit();

            return response()->json([
                'success' => true,
                'event' => $eventType,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error procesando webhook OpenWA: ' . $e->getMessage(), [
                'event_type' => $eventType,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal processing error',
            ], 500);
        }
    }

    /**
     * Procesa un mensaje recibido desde el cliente vía WhatsApp.
     */
    protected function processIncomingMessage(array $data): void
    {
        // En OpenWA, los mensajes de grupos se descartan o se diferencian
        if (! empty($data['isGroupMsg']) || (isset($data['chatId']) && str_ends_with($data['chatId'], '@g.us'))) {
            return;
        }

        $openwaMessageId = $data['id'] ?? $data['messageId'] ?? null;
        if (! $openwaMessageId) {
            return;
        }

        // Evitar duplicados si el mensaje ya fue registrado
        if (ConversationMessage::where('openwa_message_id', $openwaMessageId)->exists()) {
            return;
        }

        $senderPhoneRaw = $data['from'] ?? $data['sender']['id'] ?? $data['phone'] ?? '';
        $senderPhone = $this->openWaService->formatPhone($senderPhoneRaw);
        $customerName = $data['notifyName'] ?? $data['sender']['name'] ?? $data['sender']['pushname'] ?? 'Cliente WhatsApp';

        $body = $data['body'] ?? $data['text'] ?? $data['content'] ?? '';
        $type = $data['type'] ?? 'text';

        // 1. Identificar o vincular Cliente
        $customer = Customer::where('phone', $senderPhone)
            ->orWhere('phone', 'like', "%{$senderPhone}%")
            ->first();

        if (! $customer && ! empty($senderPhone)) {
            $customer = Customer::create([
                'name' => $customerName,
                'phone' => $senderPhone,
                'source' => 'whatsapp',
                'status' => 'active',
            ]);
        } elseif ($customer && empty($customer->name) && ! empty($customerName)) {
            $customer->update(['name' => $customerName]);
        }

        // 2. Obtener o crear Conversación activa
        $conversation = Conversation::where('customer_phone', $senderPhone)
            ->orWhere('customer_id', $customer?->id)
            ->latest('id')
            ->first();

        if (! $conversation) {
            // Intentar asociar el pedido más reciente del cliente si existe
            $latestOrder = $customer ? Order::where('customer_id', $customer->id)->latest('id')->first() : null;

            $conversation = Conversation::create([
                'customer_id' => $customer?->id,
                'order_id' => $latestOrder?->id,
                'channel' => 'whatsapp_openwa',
                'session_id' => $this->openWaService->getSessionId(),
                'customer_phone' => $senderPhone,
                'customer_name' => $customer?->name ?? $customerName,
                'order_reference' => $latestOrder?->reference_code,
                'status' => 'open',
                'unread_count' => 1,
                'last_message_preview' => mb_substr($body ?: ('[' . ucfirst($type) . ']'), 0, 150),
                'last_activity_at' => now(),
                'last_incoming_at' => now(),
            ]);
        } else {
            $conversation->increment('unread_count');
            $conversation->update([
                'status' => $conversation->status === 'resolved' ? 'open' : $conversation->status,
                'channel' => 'whatsapp_openwa',
                'last_message_preview' => mb_substr($body ?: ('[' . ucfirst($type) . ']'), 0, 150),
                'last_activity_at' => now(),
                'last_incoming_at' => now(),
            ]);
        }

        // 3. Crear el mensaje recibido
        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'direction' => 'inbound',
            'sender_type' => 'customer',
            'sender_phone' => $senderPhone,
            'openwa_message_id' => $openwaMessageId,
            'type' => $type === 'chat' ? 'text' : $type,
            'content' => $body,
            'status' => 'delivered',
            'sent_at' => isset($data['timestamp']) ? Carbon::createFromTimestamp($data['timestamp']) : now(),
            'delivered_at' => now(),
            'raw_payload' => $data,
        ]);
    }

    /**
     * Procesa un mensaje enviado (confirmación desde OpenWA).
     */
    protected function processSentMessage(array $data): void
    {
        $openwaMessageId = $data['id'] ?? $data['messageId'] ?? null;
        if (! $openwaMessageId) {
            return;
        }

        $message = ConversationMessage::where('openwa_message_id', $openwaMessageId)->first();
        if ($message) {
            $message->update([
                'status' => 'sent',
                'sent_at' => $message->sent_at ?? now(),
            ]);
        }
    }

    /**
     * Procesa actualización de acuse de recibo / lectura (ACK: 1=sent, 2=received/delivered, 3=read).
     */
    protected function processAckStatus(array $data): void
    {
        $openwaMessageId = $data['id'] ?? $data['messageId'] ?? null;
        $ack = $data['ack'] ?? $data['status'] ?? null;

        if (! $openwaMessageId || $ack === null) {
            return;
        }

        $message = ConversationMessage::where('openwa_message_id', $openwaMessageId)->first();
        if (! $message) {
            return;
        }

        $status = match ((int) $ack) {
            1 => 'sent',
            2 => 'delivered',
            3, 4 => 'read',
            -1 => 'failed',
            default => $message->status,
        };

        $updates = ['status' => $status];
        if ($status === 'delivered' && ! $message->delivered_at) {
            $updates['delivered_at'] = now();
        }
        if ($status === 'read' && ! $message->read_at) {
            $updates['read_at'] = now();
        }

        $message->update($updates);
    }

    /**
     * Procesa eventos de cambio de estado de la sesión OpenWA (WORKING, SCAN_QR_CODE, etc.).
     */
    protected function processSessionStatus(array $data): void
    {
        $sessionId = $data['session'] ?? $data['sessionId'] ?? $this->openWaService->getSessionId();
        $status = strtoupper((string) ($data['status'] ?? $data['state'] ?? 'UNKNOWN'));

        $session = WhatsappSession::getSession($sessionId);
        $isConnected = in_array($status, ['WORKING', 'CONNECTED', 'READY', 'OPEN', 'AUTHENTICATED']);

        $session->update([
            'status' => $status,
            'is_connected' => $isConnected,
            'phone_number' => $data['phone'] ?? $data['me'] ?? $session->phone_number,
            'qr_code' => $data['qrcode'] ?? $data['qr'] ?? null,
            'battery_level' => $data['battery'] ?? $session->battery_level,
            'connection_data' => $data,
            'last_active_at' => now(),
        ]);
    }
}

