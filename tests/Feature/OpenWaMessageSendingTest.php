<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenWaMessageSendingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.openwa.base_url', 'https://openwa.test');
        Config::set('services.openwa.api_key', 'test-api-key');
        Config::set('services.openwa.session_id', 'default');

        $perm = \App\Models\Permission::firstOrCreate(
            ['name' => 'whatsapp.manage'],
            ['display_name' => 'Gestionar WhatsApp', 'module' => 'whatsapp']
        );
        $role = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrador']);
        $role->permissions()->syncWithoutDetaching([$perm->id]);

        $this->user = User::factory()->create([
            'status' => 'active',
        ]);
        $this->user->roles()->attach($role);

        $customer = Customer::create([
            'name' => 'Cliente WhatsApp',
            'phone' => '51987654321',
            'status' => 'active',
        ]);

        $this->conversation = Conversation::create([
            'customer_id' => $customer->id,
            'customer_phone' => '51987654321',
            'customer_name' => 'Cliente WhatsApp',
            'status' => 'open',
            'channel' => 'whatsapp_openwa',
        ]);
    }

    public function test_send_message_dispatches_via_openwa_and_stores_in_database(): void
    {
        Http::fake([
            'https://openwa.test/api/sessions/default/messages/send-text' => Http::response([
                'id' => 'true_51987654321@c.us_OUT9911',
                'status' => 'OK',
            ], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('admin.conversations.chat.send', $this->conversation), [
                'type' => 'whatsapp',
                'content' => 'Estimado cliente, tenemos stock disponible de la laptop.',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => [
                'type' => 'text',
                'content' => 'Estimado cliente, tenemos stock disponible de la laptop.',
                'status' => 'sent',
            ],
        ]);

        // Verificar almacenamiento en base de datos
        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $this->conversation->id,
            'openwa_message_id' => 'true_51987654321@c.us_OUT9911',
            'direction' => 'outbound',
            'sender_type' => 'advisor',
            'status' => 'sent',
            'content' => 'Estimado cliente, tenemos stock disponible de la laptop.',
        ]);

        // Verificar actualización de conversación
        $this->conversation->refresh();
        $this->assertEquals('whatsapp_openwa', $this->conversation->channel);
        $this->assertEquals('in_progress', $this->conversation->status);
    }

    public function test_send_message_rejects_when_not_configured(): void
    {
        Config::set('services.openwa.api_key', null);
        Config::set('services.openwa.base_url', null);
        Config::set('services.whatsapp.enabled', false);
        Config::set('services.whatsapp.phone_number_id', null);
        Config::set('services.whatsapp.access_token', null);

        $response = $this->actingAs($this->user)
            ->postJson(route('admin.conversations.chat.send', $this->conversation), [
                'type' => 'whatsapp',
                'content' => 'Mensaje sin servicios configurados',
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['error_code' => 'NOT_CONFIGURED']);
    }
}
