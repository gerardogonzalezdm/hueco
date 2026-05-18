<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Super-admin inicial (dueño de Hueco)
        // Editar SUPERADMIN_EMAIL y SUPERADMIN_PASSWORD en .env para personalizar
        $email = env('SUPERADMIN_EMAIL', 'super@hueco.app');
        $password = env('SUPERADMIN_PASSWORD', 'changeme123');

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'role' => 'superadmin',
                'company_id' => null,
                'email_verified_at' => now(),
            ]
        );
    }
}
