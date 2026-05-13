<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

$inst = \App\Models\Institucion::create(['nombre' => 'Test', 'slug' => 'test', 'estado' => 'activa']);

$user = \App\Models\User::create([
    'name' => 'Test',
    'nombre' => 'Test',
    'email' => 'testd@test.com',
    'usuario' => 'testuserd',
    'password' => 'secret123',
    'picture' => '',
    'institucion_id' => $inst->id,
    'activo' => true,
]);

$adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
foreach (['gestionar-socios','gestionar-materiales','gestionar-prestamos','gestionar-areas','gestionar-noticias','gestionar-anotaciones','gestionar-usuarios','gestionar-multas','ver-reportes'] as $p) {
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
}
$adminRole->givePermissionTo(\Spatie\Permission\Models\Permission::all());
$user->assignRole($adminRole);

echo 'Has role admin: ' . ($user->hasRole('admin') ? 'YES' : 'NO') . PHP_EOL;
echo 'Has permission via can: ' . ($user->can('gestionar-socios') ? 'YES' : 'NO') . PHP_EOL;
echo 'Has permission via spatie: ' . ($user->hasPermissionTo('gestionar-socios') ? 'YES' : 'NO') . PHP_EOL;
echo 'All perms names: ' . json_encode($user->getPermissionNames()->toArray()) . PHP_EOL;
echo 'All roles: ' . json_encode($user->getRoleNames()->toArray()) . PHP_EOL;
