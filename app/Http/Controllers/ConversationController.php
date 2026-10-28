<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\WhatsAppCloudApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function __construct(
        protected WhatsAppCloudApiService $apiService
    ) {}

    /**
     * Bandeja de conversaciones / Chat comercial WhatsApp integrado.
     */
    public function index(Request $request): View
    {
        $query = Conversation::with(['customer', 'order', 'assignedUser'])
            ->latest('last_activity_at');

        if ($status = $request->input('status')) {
            if ($status === 'unread') {
                $query->where('unread_count', '>', 0);
            } else {
                $query->where('status', $status);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('order_reference', 'like', "%{$search}%");
            });
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        $conversations = $query->paginate(35)->withQueryString();

        $stats = [
            'open' => Conversation::where('status', 'open')->count(),
            'in_progress' => Conversation::where('status', 'in_progress')->count(),
            'resolved' => Conversation::where('status', 'resolved')->count(),
            'unread' => Conversation::where('unread_count', '>', 0)->count(),
            'total' => Conversation::count(),
        ];

        $advisors = User::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $whatsappStatus = $this->apiService->getConfigurationStatus();

        // Si hay una conversación solicitada o seleccionamos la primera disponible
        $activeConversation = null;
        if ($conversations->isNotEmpty()) {
            $activeConversation = $conversations->first();
            $activeConversation->load(['customer.notes.user', 'order.items.product', 'assignedUser', 'messages.user']);
        }

        $products = Product::published()
            ->with(['brand:id,name', 'category:id,name'])
            ->orderBy('name')
            ->get();

        $productsData = $products->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'price' => $p->formatted_price,
            'brand' => $p->brand?->name ?? 'Portátiles Perú',
            'category' => $p->category?->name ?? 'Laptops',
            'url' => route('catalogo.detalle', $p->slug),
            'pdf_url' => route('catalogo.ficha-tecnica', $p->slug),
            'specs' => $p->technical_specs ?? [],
        ]);

        $conversation = $activeConversation;

        return view('admin.conversations.show', compact(
            'conversation',
            'conversations',
            'activeConversation',
            'stats',
            'advisors',
            'whatsappStatus',
            'products',
            'productsData'
        ));
    }

    /**
     * Ficha detallada de la conversación con chat comercial integrado.
     */
    public function show(Conversation $conversation): View
    {
        $conversation->load(['customer.notes.user', 'order.items.product', 'assignedUser', 'messages.user']);

        // Marcar mensajes como leídos al entrar
        $conversation->markAsRead();

        $query = Conversation::with(['customer', 'order', 'assignedUser'])
            ->latest('last_activity_at');

        $conversations = $query->paginate(35)->withQueryString();

        $stats = [
            'open' => Conversation::where('status', 'open')->count(),
            'in_progress' => Conversation::where('status', 'in_progress')->count(),
            'resolved' => Conversation::where('status', 'resolved')->count(),
            'unread' => Conversation::where('unread_count', '>', 0)->count(),
            'total' => Conversation::count(),
        ];

        $advisors = User::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $whatsappStatus = $this->apiService->getConfigurationStatus();

        $products = Product::published()
            ->with(['brand:id,name', 'category:id,name'])
            ->orderBy('name')
            ->get();

        $productsData = $products->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'price' => $p->formatted_price,
            'brand' => $p->brand?->name ?? 'Portátiles Perú',
            'category' => $p->category?->name ?? 'Laptops',
            'url' => route('catalogo.detalle', $p->slug),
            'pdf_url' => route('catalogo.ficha-tecnica', $p->slug),
            'specs' => $p->technical_specs ?? [],
        ]);

        $activeConversation = $conversation;

        return view('admin.conversations.show', compact(
            'conversation',
            'activeConversation',
            'conversations',
            'stats',
            'advisors',
            'whatsappStatus',
            'products',
            'productsData'
        ));
    }

    /**
     * Retorna datos completos en JSON para la hidratación reactiva del chat.
     */
    public function data(Conversation $conversation): JsonResponse
    {
        $conversation->load([
            'customer.notes.user',
            'order.items.product',
            'assignedUser',
            'messages.user',
        ]);

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'customer_name' => $conversation->customer_name ?: 'Cliente WhatsApp',
                'customer_phone' => $conversation->customer_phone,
                'clean_phone' => $conversation->cleanPhone(),
                'status' => $conversation->status,
                'status_label' => $conversation->status_label,
                'status_color' => $conversation->status_color,
                'user_id' => $conversation->user_id,
                'advisor_name' => $conversation->assignedUser?->name ?? 'Sin asignar',
                'unread_count' => $conversation->unread_count,
                'is_within_24h' => $conversation->isWithin24HourWindow(),
                'window_expires_at' => $conversation->window_expires_at?->format('d/m/Y H:i'),
                'order_reference' => $conversation->order_reference,
            ],
            'customer' => $conversation->customer ? [
                'id' => $conversation->customer->id,
                'name' => $conversation->customer->name,
                'phone' => $conversation->customer->phone,
                'email' => $conversation->customer->email,
                'city' => $conversation->customer->city,
                'orders_count' => $conversation->customer->orders_count,
                'total_spent' => $conversation->customer->formatted_total_spent,
                'show_url' => route('admin.customers.show', $conversation->customer),
                'notes' => $conversation->customer->notes->map(fn ($n) => [
                    'id' => $n->id,
                    'note' => $n->note,
                    'user_name' => $n->user?->name ?? 'Personal',
                    'created_at' => $n->created_at?->format('d/m/Y H:i'),
                ]),
            ] : null,
            'order' => $conversation->order ? [
                'id' => $conversation->order->id,
                'order_number' => $conversation->order->order_number,
                'total' => $conversation->order->formatted_total,
                'status' => $conversation->order->status,
                'show_url' => route('admin.orders.show', $conversation->order),
                'items' => $conversation->order->items->map(fn ($it) => [
                    'id' => $it->id,
                    'product_name' => $it->product_name,
                    'quantity' => $it->quantity,
                    'formatted_price' => $it->formatted_price,
                    'formatted_subtotal' => $it->formatted_subtotal,
                ]),
            ] : null,
            'messages' => $conversation->messages->map(fn (ConversationMessage $msg) => [
                'id' => $msg->id,
                'direction' => $msg->direction,
                'sender_type' => $msg->sender_type,
                'type' => $msg->type,
                'content' => $msg->content,
                'status' => $msg->status,
                'attachment_url' => $msg->attachment_url,
                'attachment_type' => $msg->attachment_type,
                'attachment_filename' => $msg->attachment_filename,
                'error_message' => $msg->error_message,
                'user_name' => $msg->user?->name ?? ($msg->direction === 'inbound' ? ($conversation->customer_name ?: 'Cliente') : 'Asesor'),
                'sent_at' => $msg->sent_at?->format('d/m/Y H:i'),
                'time_only' => $msg->sent_at?->format('H:i'),
            ]),
            'whatsapp_status' => $this->apiService->getConfigurationStatus(),
        ]);
    }

    /**
     * Endpoint de sincronización asíncrona / polling ligero para actualizar mensajes en tiempo real.
     */
    public function sync(Request $request, Conversation $conversation): JsonResponse
    {
        $afterId = (int) $request->query('after_id', 0);

        $newMessages = $conversation->messages()
            ->with('user')
            ->where('id', '>', $afterId)
            ->get()
            ->map(fn (ConversationMessage $msg) => [
                'id' => $msg->id,
                'direction' => $msg->direction,
                'sender_type' => $msg->sender_type,
                'type' => $msg->type,
                'content' => $msg->content,
                'status' => $msg->status,
                'attachment_url' => $msg->attachment_url,
                'attachment_type' => $msg->attachment_type,
                'attachment_filename' => $msg->attachment_filename,
                'error_message' => $msg->error_message,
                'user_name' => $msg->user?->name ?? ($msg->direction === 'inbound' ? ($conversation->customer_name ?: 'Cliente') : 'Asesor'),
                'sent_at' => $msg->sent_at?->format('d/m/Y H:i'),
                'time_only' => $msg->sent_at?->format('H:i'),
            ]);

        // Retorna estados recientes para actualizar ticks (delivered, read, failed)
        $statuses = $conversation->messages()
            ->where('direction', 'outbound')
            ->latest('id')
            ->limit(20)
            ->get(['id', 'status', 'error_message']);

        return response()->json([
            'new_messages' => $newMessages,
            'statuses' => $statuses,
            'unread_count' => $conversation->fresh()->unread_count,
            'status' => $conversation->status,
            'is_within_24h' => $conversation->isWithin24HourWindow(),
        ]);
    }

    /**
     * Envía un mensaje de texto o registra una nota privada sin recargar la página.
     */
    public function sendChatMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $request->validate([
            'content' => 'required_without:file|nullable|string|max:4000',
            'type' => 'required|in:whatsapp,note',
            'file' => 'nullable|file|max:16384|mimes:pdf,jpg,jpeg,png,webp',
        ]);

        // 1. Guardar como Nota Interna Privada
        if ($request->type === 'note') {
            $content = (string) $request->input('content');
            $message = ConversationMessage::create([
                'conversation_id' => $conversation->id,
                'user_id' => auth()->id(),
                'direction' => 'outbound',
                'sender_type' => 'advisor',
                'type' => 'note',
                'content' => $content,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $conversation->update([
                'last_activity_at' => now(),
                'last_message_preview' => '🔒 Nota: '.mb_substr($content, 0, 100),
            ]);

            AuditLog::log('conversation.note_added', Conversation::class, $conversation->id, null, ['content' => $content]);

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'direction' => 'outbound',
                    'sender_type' => 'advisor',
                    'type' => 'note',
                    'content' => $message->content,
                    'status' => 'sent',
                    'user_name' => auth()->user()->name,
                    'sent_at' => $message->sent_at->format('d/m/Y H:i'),
                    'time_only' => $message->sent_at->format('H:i'),
                ],
            ]);
        }

        // 2. Envío real a WhatsApp Cloud API
        $content = (string) $request->input('content', '');
        $phone = $conversation->cleanPhone();

        if (! $this->apiService->isConfigured()) {
            return response()->json([
                'success' => false,
                'error' => 'WhatsApp Cloud API no está configurada. Puedes guardar notas internas o configurar las credenciales de Meta en el archivo .env.',
                'error_code' => 'NOT_CONFIGURED',
                'configuration_status' => $this->apiService->getConfigurationStatus(),
            ], 422);
        }

        $apiResult = $this->apiService->sendTextMessage($phone, $content);

        $message = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'direction' => 'outbound',
            'sender_type' => 'advisor',
            'type' => 'text',
            'content' => $content,
            'whatsapp_message_id' => $apiResult['message_id'],
            'status' => $apiResult['success'] ? 'sent' : 'failed',
            'error_code' => $apiResult['error_code'],
            'error_message' => $apiResult['error'],
            'sent_at' => now(),
            'raw_payload' => $apiResult['raw'],
        ]);

        $conversation->update([
            'last_activity_at' => now(),
            'last_message_preview' => mb_substr($content, 0, 140),
            'channel' => 'whatsapp_api',
            'status' => $conversation->status === 'open' ? 'in_progress' : $conversation->status,
        ]);

        AuditLog::log('conversation.whatsapp_sent', Conversation::class, $conversation->id, null, [
            'status' => $message->status,
            'wamid' => $message->whatsapp_message_id,
        ]);

        return response()->json([
            'success' => $apiResult['success'],
            'error' => $apiResult['error'],
            'message' => [
                'id' => $message->id,
                'direction' => 'outbound',
                'sender_type' => 'advisor',
                'type' => 'text',
                'content' => $message->content,
                'status' => $message->status,
                'error_message' => $message->error_message,
                'user_name' => auth()->user()->name,
                'sent_at' => $message->sent_at->format('d/m/Y H:i'),
                'time_only' => $message->sent_at->format('H:i'),
            ],
        ], $apiResult['success'] ? 200 : 422);
    }

    /**
     * Adjunta y envía un archivo multimedia o documento a WhatsApp.
     */
    public function attachMedia(Request $request, Conversation $conversation): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:16384|mimes:pdf,jpg,jpeg,png,webp',
            'caption' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalFilename = $file->getClientOriginalName();
        $size = $file->getSize();

        $type = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? 'image' : 'document';

        $storedPath = $file->store('whatsapp_attachments', 'public');
        $publicUrl = asset('storage/'.$storedPath);

        if (! $this->apiService->isConfigured()) {
            return response()->json([
                'success' => false,
                'error' => 'WhatsApp Cloud API no está configurada. No se pudo enviar el archivo multimedia.',
                'error_code' => 'NOT_CONFIGURED',
            ], 422);
        }

        $caption = $request->input('caption') ?: $originalFilename;
        $apiResult = $this->apiService->sendMediaMessage(
            toPhone: $conversation->cleanPhone(),
            type: $type,
            mediaUrl: $publicUrl,
            caption: $caption,
            filename: $originalFilename
        );

        $message = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'direction' => 'outbound',
            'sender_type' => 'advisor',
            'type' => $type,
            'content' => $caption,
            'attachment_url' => $publicUrl,
            'attachment_type' => $type,
            'attachment_filename' => $originalFilename,
            'attachment_size' => $size,
            'whatsapp_message_id' => $apiResult['message_id'],
            'status' => $apiResult['success'] ? 'sent' : 'failed',
            'error_code' => $apiResult['error_code'],
            'error_message' => $apiResult['error'],
            'sent_at' => now(),
            'raw_payload' => $apiResult['raw'],
        ]);

        $conversation->update([
            'last_activity_at' => now(),
            'last_message_preview' => "📎 [{$originalFilename}]",
            'channel' => 'whatsapp_api',
        ]);

        return response()->json([
            'success' => $apiResult['success'],
            'error' => $apiResult['error'],
            'message' => [
                'id' => $message->id,
                'direction' => 'outbound',
                'sender_type' => 'advisor',
                'type' => $type,
                'content' => $message->content,
                'attachment_url' => $publicUrl,
                'attachment_filename' => $originalFilename,
                'status' => $message->status,
                'error_message' => $message->error_message,
                'user_name' => auth()->user()->name,
                'sent_at' => $message->sent_at->format('d/m/Y H:i'),
                'time_only' => $message->sent_at->format('H:i'),
            ],
        ], $apiResult['success'] ? 200 : 422);
    }

    /**
     * Marca como leída la conversación tanto en base de datos como en WhatsApp Cloud API.
     */
    public function markRead(Conversation $conversation): JsonResponse
    {
        $conversation->markAsRead();

        if ($this->apiService->isConfigured()) {
            $wamids = $conversation->messages()
                ->where('direction', 'inbound')
                ->whereNotNull('whatsapp_message_id')
                ->pluck('whatsapp_message_id');

            foreach ($wamids as $wamid) {
                $this->apiService->markMessageAsRead($wamid);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Agrega una nota interna o registra una interacción en la conversación (método tradicional).
     */
    public function addMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string|max:2000',
            'type' => 'required|in:note,whatsapp_link',
        ]);

        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'direction' => 'outbound',
            'sender_type' => 'advisor',
            'type' => $request->type,
            'content' => $request->content,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $conversation->update([
            'last_activity_at' => now(),
            'last_message_preview' => substr($request->content, 0, 120),
            'status' => $conversation->status === 'open' ? 'in_progress' : $conversation->status,
        ]);
        AuditLog::log('conversation.note_added', Conversation::class, $conversation->id, null, ['type' => $request->type]);

        return back()->with('success', 'Nota registrada en la conversación.');
    }

    /**
     * Actualiza el estado de la conversación y la asignación del vendedor.
     */
    public function updateStatus(Request $request, Conversation $conversation): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,archived',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $oldStatus = $conversation->status;
        $conversation->update([
            'status' => $request->status,
            'user_id' => $request->user_id,
            'last_activity_at' => now(),
        ]);

        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'direction' => 'outbound',
            'sender_type' => 'system',
            'type' => 'status_change',
            'content' => "Estado actualizado de {$oldStatus} a {$request->status}.",
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        AuditLog::log('conversation.updated', Conversation::class, $conversation->id, ['status' => $oldStatus], ['status' => $request->status, 'user_id' => $request->user_id]);

        return back()->with('success', "Conversación actualizada a estado '".ucfirst($request->status)."'.");
    }

    /**
     * Crea manualmente una conversación para un cliente existente o nuevo.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_phone' => 'required|string|max:30',
            'customer_name' => 'required|string|max:150',
            'order_reference' => 'nullable|string|max:20',
            'summary' => 'nullable|string|max:500',
        ]);

        $customer = Customer::where('phone', $request->customer_phone)->first();

        if (! $customer) {
            $customer = Customer::create([
                'name' => $request->customer_name,
                'phone' => $request->customer_phone,
                'source' => 'manual',
                'status' => 'active',
            ]);
        }

        $order = null;
        if ($request->order_reference) {
            $order = Order::where('order_number', $request->order_reference)->first();
        }

        $conversation = Conversation::create([
            'customer_id' => $customer->id,
            'order_id' => $order?->id,
            'user_id' => auth()->id(),
            'channel' => 'whatsapp_link',
            'customer_phone' => $request->customer_phone,
            'customer_name' => $request->customer_name,
            'order_reference' => $request->order_reference,
            'status' => 'open',
            'summary' => $request->summary,
            'last_activity_at' => now(),
            'last_message_preview' => $request->summary ?? 'Conversación creada en el chat comercial.',
        ]);

        AuditLog::log('conversation.created', Conversation::class, $conversation->id, null, ['channel' => 'whatsapp_link']);

        return redirect()->route('admin.conversations.index')
            ->with('success', "Conversación abierta con {$customer->name}.");
    }
}
