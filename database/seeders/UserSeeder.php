<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Admin Principal',
                'email'    => 'admin@sanjerfit.com',
                'password' => 'password123',
                'role'     => 'Administrador',
            ],
            [
                'name'     => 'Editor Ejemplo',
                'email'    => 'editor@sanjerfit.com',
                'password' => 'password123',
                'role'     => 'Editor',
            ],
            [
                'name'     => 'Visualizador Ejemplo',
                'email'    => 'viewer@sanjerfit.com',
                'password' => 'password123',
                'role'     => 'Visualizador',
            ],
            [
                'name'     => 'Colaborador Ejemplo',
                'email'    => 'colaborador@sanjerfit.com',
                'password' => 'password123',
                'role'     => 'Colaborador',
            ],
        ];

        foreach ($users as $u) {
            $role = Role::where('name', $u['role'])->first();

            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'       => $u['name'],
                    'password'   => Hash::make($u['password']),
                    'role_id'    => $role->id,
                    'status'     => 'Activo',
                    'last_login' => null,
                ]
            );
        }
    }
}
