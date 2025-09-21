<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar tabla roles existente para compatibilidad con Spatie
        Schema::table('roles', function (Blueprint $table) {
            // Agregar columna guard_name si no existe
            if (!Schema::hasColumn('roles', 'guard_name')) {
                $table->string('guard_name')->default('web')->after('nombre');
            }
        });

        // Renombrar columnas para compatibilidad con Spatie
        Schema::table('roles', function (Blueprint $table) {
            $table->renameColumn('id_rol', 'id');
            $table->renameColumn('nombre', 'name');
        });

        // Agregar índices únicos requeridos por Spatie
        Schema::table('roles', function (Blueprint $table) {
            $table->unique(['name', 'guard_name'], 'roles_name_guard_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios para rollback
        Schema::table('roles', function (Blueprint $table) {
            // Eliminar índice único
            $table->dropUnique('roles_name_guard_name_unique');
        });

        Schema::table('roles', function (Blueprint $table) {
            // Revertir nombres de columnas
            $table->renameColumn('id', 'id_rol');
            $table->renameColumn('name', 'nombre');
        });

        Schema::table('roles', function (Blueprint $table) {
            // Eliminar columna guard_name
            if (Schema::hasColumn('roles', 'guard_name')) {
                $table->dropColumn('guard_name');
            }
        });
    }
};
