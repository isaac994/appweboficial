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
        Schema::create('compras', function (Blueprint $table) {
            $table->bigIncrements('id_compra');
            $table->bigInteger('id_proveedor')->unsigned();
            $table->bigInteger('id_usuario')->unsigned();
            $table->timestamp('fecha');
            $table->decimal('total', 12, 2);
            $table->timestamps();

            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedores');
            $table->foreign('id_usuario')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
