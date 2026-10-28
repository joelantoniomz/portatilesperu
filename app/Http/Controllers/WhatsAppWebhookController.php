<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Customer;
use App\Models\WhatsappWebhookEvent;
use App\Services\WhatsAppCloudApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        protected WhatsAppCloudApiService $apiService
    ) {}

    /**
     * Endpoint GET para la verificación inicial del Webhook por Meta.
     */
    public function verifyWebhook(Request $request): Response
    {
        // PHP convierte puntos en guiones bajos en $_GET (ej. hub.mode -> hub_mode)
        $mode = $request->query('hub_mode', $request->query('hub.mode'));
        $token = $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = $request->query('hub_challenge', $request->query('hub.challenge'));

        if ($this->apiService->verifyWebhookToken($mode, $token) && $challenge !== null) {
            return response((string) $challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        Log::warning('Fallo de verificación de token en Webhook WhatsApp', [
            'mode' => $mode,
            'ip' => $request->ip(),
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Endpoint POST para recibir eventos en tiempo real de WhatsApp (mensajes entrantes y estados de entrega).
     */
    public function handleIncoming(Request $request): JsonResponse
    {
        $rawContent = $request->getContent();
        $signatureHeader = $request->header('X-Hub-Signature-256');

        if (! $this->apiService->validateWebhookSignature($rawContent, $signatureHeader)) {
            Log::warning('Firma inválida en Webhook de WhatsApp', [
                'header' => $signatureHeader,
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = $request->json()->all();

        if (empty($payload['object']) || $payload['object'] !== 'whatsapp_business_account') {
            return response()->json(['status' => 'IGNORED_NON_WHATSAPP_OBJECT'], 200);
        }

        $entries = $payload['entry'] ?? [];
        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];
            foreach ($changes as $change) {
                $field = $change['field'] ?? '';
                if ($field !== 'messages') {
                    continue;
                }

                $value = $change['value'] ?? [];

                // 1. Procesar actualizaciones de estado de entrega (sent, delivered, read, failed)
                $this->processStatuses($value['statuses'] ?? []);

                // 2. Procesar mensajes entrantes enviados por clientes
                $this->processIncomingMessages($value);
            }
        }

        return response()->json(['status' => 'EVENT_RECEIVED'], 200);
    }

    /**
     * Procesa los estados de entrega y lectura de mensajes salientes devueltos por Meta.
     *
     * @param  array<int, array<string, mixed>>  $statuses
     */
    protected function processStatuses(array $statuses): void
    {
        foreach ($statuses as $statusItem) {
            $wamid = $statusItem['id'] ?? null;
            $newStatus = $statusItem['status'] ?? null;
            $timestamp = $statusItem['timestamp'] ?? time();

            if (! $wamid || ! $newStatus) {
                continue;
            }

            $message = ConversationMessage::where('whatsapp_message_id', $wamid)->first();
            if (! $message) {
                continue;
            }

            $updateData = ['status' => $newStatus];
            $carbonDate = Carbon::createFromTimestamp($timestamp);

            if ($newStatus === 'delivered' && ! $message->delivered_at) {
                $updateData['delivered_at'] = $carbonDate;
            } elseif ($newStatus === 'read') {
                if (! $message->delivered_at) {
                    $updateData['delivered_at'] = $carbonDate;
                }
                $updateData['read_at'] = $carbonDate;
            } elseif ($newStatus === 'failed') {
                $firstError = $statusItem['errors'][0] ?? [];
                $updateData['error_code'] = (string) ($firstError['code'] ?? 'DELIVERY_FAILED');
                $updateData['error_message'] = (string) ($firstError['title'] ?? $firstError['message'] ?? 'Fallo de entrega reportado por Meta');
            }

            $message->update($updateData);

            // Registrar evento para idempotencia
            WhatsappWebhookEvent::create([
                'event_id' => 'status_'.$wamid.'_'.$newStatus,
                'message_id' => $wamid,
                'event_type' => 'status_'.$newStatus,
                'payload' => $statusItem,
                'processed_at' => now(),
            ]);
        }
    }

    /**
     * Procesa los mensajes entrantes de clientes.
     *
     * @param  array<string, mixed>  $value
     */
    protected function processIncomingMessages(array $value): void
    {
        $messages = $value['messages'] ?? [];
        if (empty($messages)) {
            return;
        }

        $contacts = $value['contacts'] ?? [];
        $contactProfile = $contacts[0] ?? [];
        $contactName = $contactProfile['profile']['name'] ?? null;

        foreach ($messages as $msg) {
            $wamid = $msg['id'] ?? null;
            if (! $wamid) {
                continue;
            }

            // Control de idempotencia para evitar procesar mensajes duplicados
            if (ConversationMessage::where('whatsapp_message_id', $wamid)->exists()) {
                continue;
            }

            $fromPhone = (string) ($msg['from'] ?? '');
            $type = (string) ($msg['type'] ?? 'text');
            $timestamp = $msg['timestamp'] ?? time();
            $sentAt = Carbon::createFromTimestamp($timestamp);

            // Determinar o crear la conversación y cliente asociado
            $conversation = $this->resolveConversation($fromPhone, $contactName);

            // Extraer el texto y datos de adjunto según el tipo de mensaje de WhatsApp
            [$content, $attachmentUrl, $attachmentFilename, $attachmentType] = $this->extractMessageContent($type, $msg);

            // Crear el mensaje entrante en base de datos
            ConversationMessage::create([
                'conversation_id' => $conversation->id,
                'user_id' => null,
                'direction' => 'inbound',
                'sender_type' => 'customer',
                'type' => $type,
                'whatsapp_message_id' => $wamid,
                'status' => 'delivered',
                'content' => $content,
                'attachment_url' => $attachmentUrl,
                'attachment_type' => $attachmentType,
                'attachment_filename' => $attachmentFilename,
                'sent_at' => $sentAt,
                'delivered_at' => now(),
                'raw_payload' => $msg,
            ]);

            // Actualizar la conversación con la última actividad y sumar no leídos
            $conversation->update([
                'last_activity_at' => now(),
                'last_incoming_at' => now(),
                'last_message_preview' => mb_substr($content, 0, 140),
                'unread_count' => $conversation->unread_count + 1,
                'channel' => 'whatsapp_api',
                'status' => in_array($conversation->status, ['resolved', 'archived'], true) ? 'in_progress' : $conversation->status,
            ]);

            // Registrar evento de webhook
            WhatsappWebhookEvent::create([
                'event_id' => 'msg_'.$wamid,
                'message_id' => $wamid,
                'event_type' => 'message_incoming',
                'payload' => $msg,
                'processed_at' => now(),
            ]);
        }
    }

    /**
     * Localiza o genera la conversación y cliente correspondientes al teléfono.
     */
    protected function resolveConversation(string $rawPhone, ?string $contactName): Conversation
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        $peruNationalPhone = str_starts_with($cleanPhone, '51') && strlen($cleanPhone) === 11
            ? substr($cleanPhone, 2)
            : $cleanPhone;

        // 1. Buscar conversación existente por teléfono (con o sin prefijo 51)
        $conversation = Conversation::where(function ($q) use ($cleanPhone, $peruNationalPhone) {
            $q->where('customer_phone', $cleanPhone)
                ->orWhere('customer_phone', $peruNationalPhone)
                ->orWhere('customer_phone', '51'.$peruNationalPhone);
        })
            ->latest('last_activity_at')
            ->first();

        if ($conversation) {
            if ($contactName && empty($conversation->customer_name)) {
                $conversation->update(['customer_name' => $contactName]);
            }

            return $conversation;
        }

        // 2. Buscar o crear cliente en el sistema
        $customer = Customer::where(function ($q) use ($cleanPhone, $peruNationalPhone) {
            $q->where('phone', $cleanPhone)
                ->orWhere('phone', $peruNationalPhone)
                ->orWhere('phone', '51'.$peruNationalPhone);
        })->first();

        if (! $customer) {
            $customer = Customer::create([
                'name' => $contactName ?: 'Cliente WhatsApp (+'.$cleanPhone.')',
                'phone' => $peruNationalPhone,
                'source' => 'whatsapp',
                'status' => 'active',
            ]);
        }

        // 3. Crear nueva conversación
        return Conversation::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $peruNationalPhone,
            'channel' => 'whatsapp_api',
            'status' => 'open',
            'summary' => 'Conversación iniciada por cliente vía WhatsApp.',
            'last_activity_at' => now(),
            'last_incoming_at' => now(),
            'unread_count' => 0,
        ]);
    }

    /**
     * Extrae texto y metadatos de los distintos tipos de mensajes de WhatsApp.
     *
     * @param  array<string, mixed>  $msg
     * @return array{0: string, 1: ?string, 2: ?string, 3: ?string}
     */
    protected function extractMessageContent(string $type, array $msg): array
    {
        $content = '';
        $attachmentUrl = null;
        $attachmentFilename = null;
        $attachmentType = null;

        switch ($type) {
            case 'text':
                $content = (string) ($msg['text']['body'] ?? '');
                break;

            case 'image':
                $attachmentType = 'image';
                $content = ! empty($msg['image']['caption'])
                    ? $msg['image']['caption']
                    : '📷 [Imagen recibida]';
                break;

            case 'document':
                $attachmentType = 'document';
                $attachmentFilename = $msg['document']['filename'] ?? 'archivo.pdf';
                $caption = $msg['document']['caption'] ?? '';
                $content = $caption
                    ? "📄 [Documento: {$attachmentFilename}] {$caption}"
                    : "📄 [Documento: {$attachmentFilename}]";
                break;

            case 'audio':
            case 'voice':
                $attachmentType = 'audio';
                $content = '🎵 [Nota de voz / Audio recibido]';
                break;

            case 'video':
                $attachmentType = 'video';
                $caption = $msg['video']['caption'] ?? '';
                $content = $caption ? "🎥 [Video] {$caption}" : '🎥 [Video recibido]';
                break;

            case 'button':
                $content = (string) ($msg['button']['text'] ?? '🔘 [Botón seleccionado]');
                break;

            case 'interactive':
                $reply = $msg['interactive']['button_reply']['title']
                    ?? $msg['interactive']['list_reply']['title']
                    ?? 'Respuesta interactiva';
                $content = "🔘 [Opción]: {$reply}";
                break;

            default:
                $content = "💬 [Mensaje tipo: {$type}]";
                break;
        }

        return [$content, $attachmentUrl, $attachmentFilename, $attachmentType];
    }
}
