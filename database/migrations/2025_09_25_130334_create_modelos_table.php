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
        Schema::create('modelos', function (Blueprint $table) {
            $table->bigIncrements('id_modelo');
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->unsignedSmallInteger('id_marca');
            $table->timestamps();

            // Clave foránea
            $table->foreign('id_marca')->references('id_marca')->on('marcas')->onDelete('cascade');

            // Índice para mejorar el rendimiento
            $table->index('id_marca');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modelos');
    }
};
