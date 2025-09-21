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
        // Modificar la columna id_cliente para permitir valores null usando SQL directo
        DB::statement('ALTER TABLE ventas MODIFY COLUMN id_cliente BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el cambio - hacer la columna no nullable
        DB::statement('ALTER TABLE ventas MODIFY COLUMN id_cliente BIGINT UNSIGNED NOT NULL');
    }
};
