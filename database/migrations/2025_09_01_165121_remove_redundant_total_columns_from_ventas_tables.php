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
        // Eliminar columna total de la tabla ventas
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('total');
        });

        // Eliminar columna total_parcial de la tabla detalle_ventas
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->dropColumn('total_parcial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar columna total en la tabla ventas
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('total', 12, 2);
        });

        // Restaurar columna total_parcial en la tabla detalle_ventas
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->decimal('total_parcial', 12, 2);
        });
    }
};
