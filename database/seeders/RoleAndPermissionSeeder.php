<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Definir roles
        $rolesData = [
            'superadmin' => [
                'display_name' => 'Superadministrador',
                'description' => 'Acceso completo, configuración e integraciones del sistema.',
            ],
            'admin' => [
                'display_name' => 'Administrador',
                'description' => 'Gestión de catálogo, inventario, pedidos, clientes y reportes.',
            ],
            'vendedor' => [
                'display_name' => 'Vendedor',
                'description' => 'Atención de pedidos, gestión de clientes y conversaciones.',
            ],
            'almacen' => [
                'display_name' => 'Almacén',
                'description' => 'Registro de entradas, salidas, conteos y movimientos de stock.',
            ],
            'editor_catalogo' => [
                'display_name' => 'Editor de Catálogo',
                'description' => 'Gestión de productos, especificaciones, imágenes y generación de PDF.',
            ],
            'auditor' => [
                'display_name' => 'Auditor (Solo Lectura)',
                'description' => 'Consulta de información autorizada, inventario, pedidos y auditoría.',
            ],
        ];

        $roles = [];
        foreach ($rolesData as $name => $data) {
            $roles[$name] = Role::firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => $data['display_name'],
                    'description' => $data['description'],
                ]
            );
        }

        // 2. Definir permisos granulares
        $permissionsData = [
            // Catálogo
            ['name' => 'catalog.view', 'display_name' => 'Ver catálogo y productos', 'module' => 'catalogo'],
            ['name' => 'catalog.manage', 'display_name' => 'Crear y editar productos y categorías', 'module' => 'catalogo'],
            ['name' => 'catalog.pdf', 'display_name' => 'Generar y exportar catálogos en PDF', 'module' => 'catalogo'],

            // Inventario
            ['name' => 'inventory.view', 'display_name' => 'Consultar existencias y saldos', 'module' => 'inventario'],
            ['name' => 'inventory.manage', 'display_name' => 'Registrar movimientos, entradas y ajustes', 'module' => 'inventario'],

            // Pedidos
            ['name' => 'orders.view', 'display_name' => 'Consultar pedidos y solicitudes', 'module' => 'pedidos'],
            ['name' => 'orders.manage', 'display_name' => 'Actualizar estados y confirmar pedidos', 'module' => 'pedidos'],

            // Clientes
            ['name' => 'customers.view', 'display_name' => 'Consultar lista de clientes', 'module' => 'clientes'],
            ['name' => 'customers.manage', 'display_name' => 'Crear y modificar clientes', 'module' => 'clientes'],

            // WhatsApp
            ['name' => 'whatsapp.view', 'display_name' => 'Consultar bandeja y chats de WhatsApp', 'module' => 'whatsapp'],
            ['name' => 'whatsapp.manage', 'display_name' => 'Responder y gestionar conversaciones', 'module' => 'whatsapp'],

            // Reportes
            ['name' => 'reports.view', 'display_name' => 'Consultar reportes y estadísticas', 'module' => 'reportes'],

            // Usuarios y Sistema
            ['name' => 'users.manage', 'display_name' => 'Administrar usuarios y roles', 'module' => 'usuarios'],
            ['name' => 'settings.manage', 'display_name' => 'Modificar configuración comercial', 'module' => 'configuracion'],
            ['name' => 'audit.view', 'display_name' => 'Consultar bitácora de auditoría', 'module' => 'auditoria'],
        ];

        $allPermissions = [];
        foreach ($permissionsData as $perm) {
            $permissionModel = Permission::firstOrCreate(
                ['name' => $perm['name']],
                [
                    'display_name' => $perm['display_name'],
                    'module' => $perm['module'],
                    'description' => 'Permite ' . strtolower($perm['display_name']),
                ]
            );
            $allPermissions[$perm['name']] = $permissionModel->id;
        }

        // 3. Asignar permisos a cada rol
        // Superadmin: todos
        $roles['superadmin']->permissions()->sync(array_values($allPermissions));

        // Admin: catálogo, inventario, pedidos, clientes, reportes, auditoria, settings
        $adminPerms = [
            'catalog.view', 'catalog.manage', 'catalog.pdf',
            'inventory.view', 'inventory.manage',
            'orders.view', 'orders.manage',
            'customers.view', 'customers.manage',
            'whatsapp.view', 'whatsapp.manage',
            'reports.view', 'audit.view', 'settings.manage',
        ];
        $roles['admin']->permissions()->sync(array_intersect_key($allPermissions, array_flip($adminPerms)));

        // Vendedor
        $vendedorPerms = [
            'catalog.view', 'catalog.pdf',
            'inventory.view',
            'orders.view', 'orders.manage',
            'customers.view', 'customers.manage',
            'whatsapp.view', 'whatsapp.manage',
        ];
        $roles['vendedor']->permissions()->sync(array_intersect_key($allPermissions, array_flip($vendedorPerms)));

        // Almacén
        $almacenPerms = [
            'catalog.view',
            'inventory.view', 'inventory.manage',
            'orders.view',
        ];
        $roles['almacen']->permissions()->sync(array_intersect_key($allPermissions, array_flip($almacenPerms)));

        // Editor de catálogo
        $editorPerms = [
            'catalog.view', 'catalog.manage', 'catalog.pdf',
        ];
        $roles['editor_catalogo']->permissions()->sync(array_intersect_key($allPermissions, array_flip($editorPerms)));

        // Auditor (Solo lectura)
        $auditorPerms = [
            'catalog.view',
            'inventory.view',
            'orders.view',
            'customers.view',
            'reports.view',
            'audit.view',
        ];
        $roles['auditor']->permissions()->sync(array_intersect_key($allPermissions, array_flip($auditorPerms)));

        // 4. Crear usuarios para pruebas y desarrollo
        $defaultPassword = Hash::make('Portatiles2026!');

        $usersData = [
            [
                'name' => 'Carlos Mendoza (Superadmin)',
                'email' => 'superadmin@portatilesperu.com',
                'phone' => '+51987654321',
                'role' => 'superadmin',
            ],
            [
                'name' => 'Mariana Torres (Admin)',
                'email' => 'admin@portatilesperu.com',
                'phone' => '+51987654322',
                'role' => 'admin',
            ],
            [
                'name' => 'Luis Paredes (Vendedor)',
                'email' => 'vendedor@portatilesperu.com',
                'phone' => '+51987654323',
                'role' => 'vendedor',
            ],
            [
                'name' => 'Jorge Huamán (Almacén)',
                'email' => 'almacen@portatilesperu.com',
                'phone' => '+51987654324',
                'role' => 'almacen',
            ],
            [
                'name' => 'Sofía Díaz (Editora de Catálogo)',
                'email' => 'editor@portatilesperu.com',
                'phone' => '+51987654325',
                'role' => 'editor_catalogo',
            ],
            [
                'name' => 'Rodrigo Castro (Auditor)',
                'email' => 'auditor@portatilesperu.com',
                'phone' => '+51987654326',
                'role' => 'auditor',
            ],
        ];

        foreach ($usersData as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $defaultPassword,
                    'phone' => $userData['phone'],
                    'status' => 'active',
                ]
            );

            $user->roles()->sync([$roles[$userData['role']]->id]);
        }
    }
}
