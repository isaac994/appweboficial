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
            // Eliminar la clave foránea primero
            if (Schema::hasColumn('productos', 'id_marca')) {
                $table->dropForeign(['id_marca']);
                $table->dropIndex(['id_marca']);
                $table->dropColumn('id_marca');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->unsignedSmallInteger('id_marca')->nullable()->after('id_categoria');
            $table->foreign('id_marca')->references('id_marca')->on('marcas')->onDelete('cascade');
            $table->index('id_marca');
        });
    }
};
