<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name'        => 'Administrador',
                'description' => 'Acceso total al sistema. Puede crear, editar y eliminar usuarios, gestionar todos los módulos.',
            ],
            [
                'name'        => 'Editor',
                'description' => 'Puede registrar colaboradores, gestionar inventario y entregar premios. No puede acceder a reportes avanzados.',
            ],
            [
                'name'        => 'Visualizador',
                'description' => 'Solo puede visualizar información. No puede realizar cambios en el sistema.',
            ],
            [
                'name'        => 'Colaborador',
                'description' => 'Puede colaborar en tareas específicas según asignación. Acceso limitado.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
