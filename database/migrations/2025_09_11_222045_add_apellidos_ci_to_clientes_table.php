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
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('apellidos', 100)->nullable()->after('nombre');
            $table->string('ci', 20)->nullable()->after('apellidos');
        });

        // Agregar restricción unique después de actualizar los datos existentes
        Schema::table('clientes', function (Blueprint $table) {
            $table->unique('ci', 'clientes_ci_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique('clientes_ci_unique');
            $table->dropColumn(['apellidos', 'ci']);
        });
    }
};
