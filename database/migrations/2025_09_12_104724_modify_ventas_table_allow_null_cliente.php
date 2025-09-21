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
        Schema::table('ventas', function (Blueprint $table) {
            // Modificar la columna id_cliente para permitir valores null
            $table->unsignedBigInteger('id_cliente')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Revertir el cambio - hacer la columna no nullable
            $table->unsignedBigInteger('id_cliente')->nullable(false)->change();
        });
    }
};
