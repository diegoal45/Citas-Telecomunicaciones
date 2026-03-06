<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

use App\Models\User;
use Illuminate\Support\Str;

echo "=== Creando 8 usuarios de prueba ===\n\n";

$testUsers = [
    ['name' => 'Juan Técnico', 'email' => 'juan.tech@excitel.com', 'phone' => '3001111111', 'id_rol' => 3],     // tecnico
    ['name' => 'Maria Lider', 'email' => 'maria.lider@excitel.com', 'phone' => '3002222222', 'id_rol' => 2],     // tecnico_lider
    ['name' => 'Carlos Cliente', 'email' => 'carlos.cliente@excitel.com', 'phone' => '3003333333', 'id_rol' => 4], // cliente
    ['name' => 'Ana Técnica', 'email' => 'ana.tech@excitel.com', 'phone' => '3004444444', 'id_rol' => 3],        // tecnico
    ['name' => 'Luis Lider', 'email' => 'luis.lider@excitel.com', 'phone' => '3005555555', 'id_rol' => 2],       // tecnico_lider
    ['name' => 'Sofia Cliente', 'email' => 'sofia.cliente@excitel.com', 'phone' => '3006666666', 'id_rol' => 4], // cliente
    ['name' => 'Pedro Técnico', 'email' => 'pedro.tech@excitel.com', 'phone' => '3007777777', 'id_rol' => 3],    // tecnico
    ['name' => 'Laura Cliente', 'email' => 'laura.cliente@excitel.com', 'phone' => '3008888888', 'id_rol' => 4], // cliente
];

$password = password_hash('password123', PASSWORD_BCRYPT);

foreach ($testUsers as $userData) {
    $user = User::create([
        'name' => $userData['name'],
        'email' => $userData['email'],
        'phone' => $userData['phone'],
        'password' => $password,
        'id_rol' => $userData['id_rol'],
    ]);
    
    $roleName = match($userData['id_rol']) {
        1 => 'admin',
        2 => 'Técnico Lider',
        3 => 'Técnico',
        4 => 'Cliente',
        default => 'Desconocido'
    };
    
    echo "✓ Usuario creado: {$user->name} ({$roleName}) - {$user->email}\n";
}

echo "\n=== Total: 8 usuarios creados correctamente ===\n";
echo "\nCredenciales de prueba:\n";
echo "Contraseña: password123\n\n";

foreach ($testUsers as $userData) {
    echo "  • {$userData['email']}\n";
}
