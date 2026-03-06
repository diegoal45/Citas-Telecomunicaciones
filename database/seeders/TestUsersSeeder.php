<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $testUsers = [
            ['name' => 'Juan Técnico', 'email' => 'juan.tech@excitel.com', 'phone' => '3001111111', 'id_rol' => 3],
            ['name' => 'Maria Lider', 'email' => 'maria.lider@excitel.com', 'phone' => '3002222222', 'id_rol' => 2],
            ['name' => 'Carlos Cliente', 'email' => 'carlos.cliente@excitel.com', 'phone' => '3003333333', 'id_rol' => 4],
            ['name' => 'Ana Técnica', 'email' => 'ana.tech@excitel.com', 'phone' => '3004444444', 'id_rol' => 3],
            ['name' => 'Luis Lider', 'email' => 'luis.lider@excitel.com', 'phone' => '3005555555', 'id_rol' => 2],
            ['name' => 'Sofia Cliente', 'email' => 'sofia.cliente@excitel.com', 'phone' => '3006666666', 'id_rol' => 4],
            ['name' => 'Pedro Técnico', 'email' => 'pedro.tech@excitel.com', 'phone' => '3007777777', 'id_rol' => 3],
            ['name' => 'Laura Cliente', 'email' => 'laura.cliente@excitel.com', 'phone' => '3008888888', 'id_rol' => 4],
        ];

        foreach ($testUsers as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['password' => bcrypt('password123')])
            );
        }
    }
}
