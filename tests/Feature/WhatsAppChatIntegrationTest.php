<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppChatIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $advisor;

    private User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = collect(['customers.view', 'customers.manage', 'whatsapp.view', 'whatsapp.manage'])
            ->mapWithKeys(fn (string $name) => [$name => Permission::create(['name' => $name, 'display_name' => $name, 'module' => 'test'])]);

        $advisorRole = Role::create(['name' => 'advisor', 'display_name' => 'Asesor']);
        $advisorRole->permissions()->sync($permissions->pluck('id'));
        $this->advisor = User::factory()->create(['status' => 'active']);
        $this->advisor->roles()->attach($advisorRole);

        $viewerRole = Role::create(['name' => 'customer-viewer', 'display_name' => 'Consulta clientes']);
        $viewerRole->permissions()->attach($permissions['customers.view']);
        $this->viewer = User::factory()->create(['status' => 'active']);
        $this->viewer->roles()->attach($viewerRole);

        Config::set('services.whatsapp.webhook_verify_token', 'token_webhook_secreto_2026');
    }

    public function test_webhook_challenge_verification_succeeds_with_valid_token(): void
    {
        $response = $this->get('/webhook/whatsapp?hub_mode=subscribe&hub_verify_token=token_webhook_secreto_2026&hub_challenge=CHALLENGE_STRING_123');

        $response->assertStatus(200);
        $this->assertSame('CHALLENGE_STRING_123', $response->getContent());
    }

    public function test_webhook_challenge_fails_with_invalid_token(): void
    {
        $response = $this->get('/webhook/whatsapp?hub_mode=subscribe&hub_verify_token=token_invalido&hub_challenge=123');

        $response->assertStatus(403);
    }

    public function test_incoming_webhook_registers_customer_conversation_and_message(): void
    {
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => 'WABA_100',
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'metadata' => ['display_phone_number' => '51987654321', 'phone_number_id' => '1001'],
                                'contacts' => [
                                    [
                                        'profile' => ['name' => 'Luciana Vega'],
                                        'wa_id' => '51999111222',
                                    ],
                                ],
                                'messages' => [
                                    [
                                        'from' => '51999111222',
                                        'id' => 'wamid.HBgLMTEyMjMzNDQ1NQ==',
                                        'timestamp' => 1730000000,
                                        'type' => 'text',
                                        'text' => ['body' => 'Hola, deseo cotizar una laptop Lenovo Legion.'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/webhook/whatsapp', $payload);

        $response->assertStatus(200)->assertJson(['status' => 'EVENT_RECEIVED']);

        $customer = Customer::where('phone', '999111222')->first();
        $this->assertNotNull($customer);
        $this->assertSame('Luciana Vega', $customer->name);

        $conversation = Conversation::where('customer_phone', '999111222')->first();
        $this->assertNotNull($conversation);
        $this->assertSame(1, $conversation->unread_count);
        $this->assertSame('whatsapp_api', $conversation->channel);

        $message = ConversationMessage::where('whatsapp_message_id', 'wamid.HBgLMTEyMjMzNDQ1NQ==')->first();
        $this->assertNotNull($message);
        $this->assertSame('inbound', $message->direction);
        $this->assertSame('customer', $message->sender_type);
        $this->assertSame('Hola, deseo cotizar una laptop Lenovo Legion.', $message->content);
    }

    public function test_incoming_webhook_is_idempotent_and_does_not_duplicate_messages(): void
    {
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => 'WABA_100',
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'messages' => [
                                    [
                                        'from' => '51988888888',
                                        'id' => 'wamid.IDEMPOTENCY_TEST_001',
                                        'timestamp' => 1730000000,
                                        'type' => 'text',
                                        'text' => ['body' => 'Mensaje repetido.'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // Primera llamada
        $this->postJson('/webhook/whatsapp', $payload)->assertOk();
        $this->assertSame(1, ConversationMessage::where('whatsapp_message_id', 'wamid.IDEMPOTENCY_TEST_001')->count());

        // Segunda llamada repetida con el mismo wamid
        $this->postJson('/webhook/whatsapp', $payload)->assertOk();
        $this->assertSame(1, ConversationMessage::where('whatsapp_message_id', 'wamid.IDEMPOTENCY_TEST_001')->count());
    }

    public function test_webhook_updates_message_delivery_statuses(): void
    {
        $conversation = Conversation::create([
            'customer_name' => 'Pedro Castillo',
            'customer_phone' => '955443322',
            'status' => 'in_progress',
        ]);

        $message = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'direction' => 'outbound',
            'sender_type' => 'advisor',
            'type' => 'text',
            'content' => 'Buenas tardes, te adjunto la proforma.',
            'whatsapp_message_id' => 'wamid.STATUS_UPDATE_TEST',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $statusPayload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'statuses' => [
                                    [
                                        'id' => 'wamid.STATUS_UPDATE_TEST',
                                        'status' => 'read',
                                        'timestamp' => 1730000100,
                                        'recipient_id' => '51955443322',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->postJson('/webhook/whatsapp', $statusPayload)->assertOk();

        $message->refresh();
        $this->assertSame('read', $message->status);
        $this->assertNotNull($message->read_at);
        $this->assertNotNull($message->delivered_at);
    }

    public function test_advisor_can_fetch_chat_data_and_sync_endpoints(): void
    {
        $conversation = Conversation::create([
            'customer_name' => 'Cliente Chat',
            'customer_phone' => '977665544',
            'status' => 'open',
        ]);

        $msg1 = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'direction' => 'inbound',
            'sender_type' => 'customer',
            'type' => 'text',
            'content' => 'Mensaje 1',
            'status' => 'delivered',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->advisor)
            ->getJson(route('admin.conversations.data', $conversation));

        $response->assertOk()
            ->assertJsonPath('conversation.customer_name', 'Cliente Chat')
            ->assertJsonCount(1, 'messages');

        // Sincronización asíncrona
        $msg2 = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'direction' => 'outbound',
            'sender_type' => 'advisor',
            'type' => 'text',
            'content' => 'Mensaje 2 nuevo',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $syncResponse = $this->actingAs($this->advisor)
            ->getJson(route('admin.conversations.sync', ['conversation' => $conversation, 'after_id' => $msg1->id]));

        $syncResponse->assertOk()
            ->assertJsonCount(1, 'new_messages')
            ->assertJsonPath('new_messages.0.content', 'Mensaje 2 nuevo');
    }

    public function test_advisor_can_save_private_internal_note_without_redirecting(): void
    {
        $conversation = Conversation::create([
            'customer_name' => 'Cliente Nota',
            'customer_phone' => '911223344',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->advisor)
            ->postJson(route('admin.conversations.chat.send', $conversation), [
                'type' => 'note',
                'content' => 'Nota confidencial: cliente solicita factura con RUC 20601234567.',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message.type', 'note');

        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'type' => 'note',
            'content' => 'Nota confidencial: cliente solicita factura con RUC 20601234567.',
        ]);
    }

    public function test_advisor_sending_whatsapp_without_credentials_returns_clear_configuration_error(): void
    {
        Config::set('services.whatsapp.enabled', false);

        $conversation = Conversation::create([
            'customer_name' => 'Cliente Sin Config',
            'customer_phone' => '911223344',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->advisor)
            ->postJson(route('admin.conversations.chat.send', $conversation), [
                'type' => 'whatsapp',
                'content' => 'Hola, intentando enviar sin credenciales.',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'NOT_CONFIGURED');
    }

    public function test_advisor_can_send_whatsapp_message_when_configured(): void
    {
        Config::set('services.whatsapp', [
            'enabled' => true,
            'version' => 'v22.0',
            'phone_number_id' => '10001',
            'access_token' => 'TEST_TOKEN',
            'base_url' => 'https://graph.facebook.com',
        ]);

        Http::fake([
            'https://graph.facebook.com/v22.0/10001/messages' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.SENT_SUCCESS_TEST_999']],
            ], 200),
        ]);

        $conversation = Conversation::create([
            'customer_name' => 'Cliente Configurado',
            'customer_phone' => '922334455',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->advisor)
            ->postJson(route('admin.conversations.chat.send', $conversation), [
                'type' => 'whatsapp',
                'content' => 'Hola, tenemos disponibilidad inmediata de la laptop ASUS Zenbook.',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message.status', 'sent');

        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'whatsapp_message_id' => 'wamid.SENT_SUCCESS_TEST_999',
            'status' => 'sent',
        ]);
    }
}
