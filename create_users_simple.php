<?php

// Laravel Bootstrap
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

use App\Models\User;

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

echo "=== Creando 8 usuarios de prueba ===\n\n";

$created = 0;
$skipped = 0;

foreach ($testUsers as $data) {
    try {
        $exists = User::where('email', $data['email'])->exists();
        if ($exists) {
            echo "⊘ {$data['name']} ya existe\n";
            $skipped++;
        } else {
            User::create(array_merge($data, ['password' => bcrypt('password123')]));
            echo "✓ {$data['name']} creado\n";
            $created++;
        }
    } catch (\Exception $e) {
        echo "✗ Error al crear {$data['name']}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Resumen ===\n";
echo "Nuevos: $created\n";
echo "Existentes: $skipped\n";
echo "Total: " . User::count() . " usuarios en base de datos\n\n";

echo "Credenciales de prueba: password123\n";
