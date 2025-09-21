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
        Schema::create('productos', function (Blueprint $table) {
            $table->bigIncrements('id_producto');
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio_venta', 10, 2);
            $table->unsignedSmallInteger('id_categoria');
            $table->unsignedSmallInteger('id_marca')->nullable();
            $table->string('img_url')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            // Claves foráneas
            $table->foreign('id_categoria')->references('id_categoria')->on('categorias')->onDelete('cascade');
            $table->foreign('id_marca')->references('id_marca')->on('marcas')->onDelete('cascade');

            // Índices para mejorar el rendimiento
            $table->index('id_categoria');
            $table->index('id_marca');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
