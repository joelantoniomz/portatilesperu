<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_superadmin_has_unrestricted_access(): void
    {
        $superadmin = User::where('email', 'superadmin@portatilesperu.com')->first();

        $this->actingAs($superadmin)->get('/admin')->assertStatus(200);
        $this->actingAs($superadmin)->get('/admin/usuarios')->assertStatus(200);
        $this->actingAs($superadmin)->get('/admin/auditoria')->assertStatus(200);
        $this->actingAs($superadmin)->get('/admin/almacen-test')->assertStatus(200);
        $this->actingAs($superadmin)->get('/admin/vendedor-test')->assertStatus(200);
    }

    public function test_admin_can_access_users_and_audit(): void
    {
        $admin = User::where('email', 'admin@portatilesperu.com')->first();

        $this->actingAs($admin)->get('/admin')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/usuarios')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/auditoria')->assertStatus(200);
    }

    public function test_vendedor_is_forbidden_from_users_management_and_almacen(): void
    {
        $vendedor = User::where('email', 'vendedor@portatilesperu.com')->first();

        // Acceso permitido a su sección de ventas
        $this->actingAs($vendedor)->get('/admin/vendedor-test')->assertStatus(200);

        // Rechazo 403 en servidor en secciones no autorizadas
        $this->actingAs($vendedor)->get('/admin/usuarios')->assertStatus(403);
        $this->actingAs($vendedor)->get('/admin/almacen-test')->assertStatus(403);
        $this->actingAs($vendedor)->get('/admin/auditoria')->assertStatus(403);
    }

    public function test_almacen_is_forbidden_from_sales_and_users(): void
    {
        $almacen = User::where('email', 'almacen@portatilesperu.com')->first();

        // Acceso permitido a almacén
        $this->actingAs($almacen)->get('/admin/almacen-test')->assertStatus(200);

        // Rechazo 403 en servidor en secciones no autorizadas
        $this->actingAs($almacen)->get('/admin/usuarios')->assertStatus(403);
        $this->actingAs($almacen)->get('/admin/vendedor-test')->assertStatus(403);
        $this->actingAs($almacen)->get('/admin/auditoria')->assertStatus(403);
    }

    public function test_auditor_can_view_audit_logs_but_cannot_access_restricted_modules(): void
    {
        $auditor = User::where('email', 'auditor@portatilesperu.com')->first();

        // Auditor tiene permiso audit.view
        $this->actingAs($auditor)->get('/admin/auditoria')->assertStatus(200);

        // Rechazo 403 en servidor para usuarios, almacén y ventas
        $this->actingAs($auditor)->get('/admin/usuarios')->assertStatus(403);
        $this->actingAs($auditor)->get('/admin/almacen-test')->assertStatus(403);
        $this->actingAs($auditor)->get('/admin/vendedor-test')->assertStatus(403);
    }
}
