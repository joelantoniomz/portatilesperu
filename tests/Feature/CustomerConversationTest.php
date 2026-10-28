<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerConversationTest extends TestCase
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
    }

    public function test_authorized_user_can_register_and_view_a_manual_customer(): void
    {
        $response = $this->actingAs($this->advisor)->post(route('admin.customers.store'), [
            'name' => 'Ana Quispe',
            'phone' => '999 111 222',
            'email' => 'ana@example.test',
            'city' => 'Lima',
        ]);

        $customer = Customer::firstOrFail();
        $response->assertRedirect(route('admin.customers.show', $customer));
        $this->assertSame('manual', $customer->source);
        $this->actingAs($this->advisor)->get(route('admin.customers.show', $customer))->assertSee('Ana Quispe');
    }

    public function test_customer_requires_a_phone_or_email_when_created_manually(): void
    {
        $this->actingAs($this->advisor)->from(route('admin.customers.index'))->post(route('admin.customers.store'), [
            'name' => 'Sin contacto',
        ])->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('customers', 0);
    }

    public function test_customer_notes_are_stored_with_the_authenticated_advisor(): void
    {
        $customer = Customer::create(['name' => 'Cliente nota', 'phone' => '987654321']);

        $this->actingAs($this->advisor)->post(route('admin.customers.notes.store', $customer), [
            'note' => 'Prefiere coordinación por la tarde.',
            'type' => 'followup',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('customer_notes', ['customer_id' => $customer->id, 'user_id' => $this->advisor->id, 'type' => 'followup']);
    }

    public function test_conversation_can_be_created_assigned_and_logged_without_cloud_api(): void
    {
        $this->actingAs($this->advisor)->post(route('admin.conversations.store'), [
            'customer_name' => 'Cliente WhatsApp',
            'customer_phone' => '988777666',
            'summary' => 'Consulta por disponibilidad.',
        ])->assertRedirect(route('admin.conversations.index'));

        $conversation = Conversation::firstOrFail();
        $this->assertSame('whatsapp_link', $conversation->channel);

        $this->actingAs($this->advisor)->patch(route('admin.conversations.update', $conversation), [
            'status' => 'in_progress',
            'user_id' => $this->advisor->id,
        ])->assertSessionHas('success');

        $this->actingAs($this->advisor)->post(route('admin.conversations.messages.store', $conversation), [
            'type' => 'note',
            'content' => 'Se envió la ficha técnica.',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('conversation_messages', ['conversation_id' => $conversation->id, 'type' => 'note']);
        $this->assertSame('in_progress', $conversation->fresh()->status);
    }

    public function test_authorized_user_can_view_conversation_detail_page(): void
    {
        $brand = Brand::create(['name' => 'Lenovo', 'slug' => 'lenovo']);
        $category = Category::create(['name' => 'Gamer', 'slug' => 'gamer']);

        $product = Product::create([
            'sku' => 'LEN-LEGION-PRO',
            'name' => 'Lenovo Legion Pro 5',
            'slug' => 'lenovo-legion-pro-5',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 5999.00,
            'currency' => 'PEN',
            'status' => 'published',
            'technical_specs' => [
                'processor' => 'AMD Ryzen 7 7745HX',
                'ram' => '32GB DDR5',
                'storage' => '1TB SSD NVMe',
            ],
        ]);

        $conversation = Conversation::create([
            'customer_name' => 'Joel Mozombite',
            'customer_phone' => '934411541',
            'channel' => 'whatsapp_link',
            'status' => 'in_progress',
        ]);

        $this->actingAs($this->advisor)
            ->get(route('admin.conversations.show', $conversation))
            ->assertOk()
            ->assertSee('Joel Mozombite')
            ->assertSee('934411541')
            ->assertSee('Lenovo Legion Pro 5')
            ->assertSee($product->formatted_price)
            ->assertSee('AMD Ryzen 7 7745HX');
    }

    public function test_permissions_are_enforced_for_customers_and_conversations(): void
    {
        $this->actingAs($this->viewer)->get(route('admin.customers.index'))->assertOk();
        $this->actingAs($this->viewer)->post(route('admin.customers.store'), ['name' => 'No autorizado', 'phone' => '999999999'])->assertForbidden();
        $this->actingAs($this->viewer)->get(route('admin.conversations.index'))->assertForbidden();
    }
}
