<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('PORTÁTILES PERÚ');
        $response->assertSee('Acceso Administrativo');
    }

    public function test_users_can_authenticate_using_valid_credentials(): void
    {
        $user = User::where('email', 'superadmin@portatilesperu.com')->first();

        $response = $this->post('/login', [
            'email' => 'superadmin@portatilesperu.com',
            'password' => 'Portatiles2026!',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/admin');

        // Verificar que se registró el evento de auditoría
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'login',
        ]);
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'superadmin@portatilesperu.com',
            'password' => 'WrongPassword!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');

        // Verificar registro de intento fallido en auditoría
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'failed_login',
        ]);
    }

    public function test_inactive_users_cannot_access_even_with_valid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'inactivo@portatilesperu.com',
            'password' => bcrypt('Password123!'),
            'status' => 'inactive',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactivo@portatilesperu.com',
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_users_can_logout(): void
    {
        $user = User::where('email', 'admin@portatilesperu.com')->first();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'logout',
        ]);
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }
}
