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
            $table->decimal('precio_compra', 10, 2);
            $table->decimal('precio_venta', 10, 2);
            $table->unsignedSmallInteger('id_categoria');
            $table->unsignedSmallInteger('id_marca')->nullable();
            $table->unsignedBigInteger('id_proveedor')->nullable();
            $table->string('img_url')->nullable();
            $table->timestamps();

            // Claves foráneas
            $table->foreign('id_categoria')->references('id_categoria')->on('categorias')->onDelete('cascade');
            $table->foreign('id_marca')->references('id_marca')->on('marcas')->onDelete('cascade');
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedores')->onDelete('cascade');

            // Índices para mejorar el rendimiento
            $table->index('id_categoria');
            $table->index('id_marca');
            $table->index('id_proveedor');
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
