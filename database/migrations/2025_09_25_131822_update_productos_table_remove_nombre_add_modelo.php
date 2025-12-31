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
        Schema::table('productos', function (Blueprint $table) {
            // Eliminar el campo nombre
            $table->dropColumn('nombre');

            // Agregar el campo id_modelo
            $table->unsignedBigInteger('id_modelo')->after('descripcion');

            // Agregar clave foránea
            $table->foreign('id_modelo')->references('id_modelo')->on('modelos')->onDelete('cascade');

            // Agregar índice
            $table->index('id_modelo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Eliminar clave foránea e índice
            $table->dropForeign(['id_modelo']);
            $table->dropIndex(['id_modelo']);

            // Eliminar el campo id_modelo
            $table->dropColumn('id_modelo');

            // Restaurar el campo nombre
            $table->string('nombre', 150);
        });
    }
};
