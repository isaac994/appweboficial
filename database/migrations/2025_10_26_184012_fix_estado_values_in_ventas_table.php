<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Corregir valores NULL en el campo estado
        DB::statement("UPDATE ventas SET estado = 0 WHERE estado IS NULL");

        // Asegurar que el campo tenga un valor por defecto
        Schema::table('ventas', function (Blueprint $table) {
            $table->boolean('estado')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hacer nada en el rollback para mantener los datos
    }
};








