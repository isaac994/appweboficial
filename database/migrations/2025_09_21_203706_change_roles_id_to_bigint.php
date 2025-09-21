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
        // Primero eliminar la foreign key constraint de users.id_rol
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_rol']);
        });

        // Cambiar el tipo de id de smallint a bigint para compatibilidad con Spatie
        Schema::table('roles', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });

        // Cambiar el tipo de id_rol en users para que coincida
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id_rol')->change();
        });

        // Recrear la foreign key constraint
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_rol')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la foreign key constraint
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_rol']);
        });

        // Revertir el tipo de id a smallint
        Schema::table('roles', function (Blueprint $table) {
            $table->smallIncrements('id')->change();
        });

        // Revertir el tipo de id_rol en users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('id_rol')->change();
        });

        // Recrear la foreign key constraint original
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_rol')->references('id')->on('roles');
        });
    }
};
