<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'usuario')) {
                $table->string('usuario')->nullable()->unique()->after('nombre');
            }
            if (!Schema::hasColumn('users', 'picture')) {
                $table->string('picture')->nullable()->after('usuario');
            }
            if (!Schema::hasColumn('users', 'telefono')) {
                $table->string('telefono')->nullable()->after('picture');
            }
        });

        $this->migrateBibliotecarios();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(array_filter(['usuario', 'picture', 'telefono'], fn($col) => Schema::hasColumn('users', $col)));
        });
    }

    private function migrateBibliotecarios(): void
    {
        $bibliotecarios = DB::table('bibliotecarios')->get();

        foreach ($bibliotecarios as $bib) {
            if (DB::table('users')->where('email', $bib->email)->exists()) {
                continue;
            }

            $userId = DB::table('users')->insertGetId([
                'name'           => $bib->nombre,
                'nombre'         => $bib->nombre,
                'email'          => $bib->email,
                'usuario'        => $bib->usuario,
                'password'       => $bib->password,
                'picture'        => $bib->picture ?? '',
                'telefono'       => $bib->telefono ?? null,
                'activo'         => true,
                'institucion_id' => $bib->institucion_id,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Migrate existing role assignments from Bibliotecario model type
            $existingRoles = DB::table('model_has_roles')
                ->where('model_type', 'App\\Models\\Bibliotecario')
                ->where('model_id', $bib->id)
                ->get();

            foreach ($existingRoles as $assignment) {
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id'    => $assignment->role_id,
                    'model_type' => 'App\\Models\\User',
                    'model_id'   => $userId,
                ]);
            }

            // Default to admin if no roles were assigned
            if ($existingRoles->isEmpty()) {
                $adminRole = DB::table('roles')
                    ->where('name', 'admin')
                    ->where('guard_name', 'web')
                    ->first();

                if ($adminRole) {
                    DB::table('model_has_roles')->insertOrIgnore([
                        'role_id'    => $adminRole->id,
                        'model_type' => 'App\\Models\\User',
                        'model_id'   => $userId,
                    ]);
                }
            }
        }
    }
};
