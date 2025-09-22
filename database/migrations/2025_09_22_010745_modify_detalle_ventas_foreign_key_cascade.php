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
        Schema::table('detalle_ventas', function (Blueprint $table) {
            // Eliminar la clave foránea existente
            $table->dropForeign(['id_venta']);

            // Crear la nueva clave foránea con CASCADE
            $table->foreign('id_venta')
                  ->references('id_venta')
                  ->on('ventas')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            // Eliminar la clave foránea con CASCADE
            $table->dropForeign(['id_venta']);

            // Restaurar la clave foránea original sin CASCADE
            $table->foreign('id_venta')
                  ->references('id_venta')
                  ->on('ventas');
        });
    }
};
