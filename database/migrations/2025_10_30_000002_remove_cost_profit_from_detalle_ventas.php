<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_ventas', 'ganancia_total')) {
                $table->dropColumn('ganancia_total');
            }
            if (Schema::hasColumn('detalle_ventas', 'ganancia_unidad')) {
                $table->dropColumn('ganancia_unidad');
            }
            if (Schema::hasColumn('detalle_ventas', 'precio_compra')) {
                $table->dropColumn('precio_compra');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('detalle_ventas', 'precio_compra')) {
                $table->decimal('precio_compra', 10, 2)->nullable()->after('precio_unitario');
            }
            if (!Schema::hasColumn('detalle_ventas', 'ganancia_unidad')) {
                $table->decimal('ganancia_unidad', 10, 2)->nullable()->after('precio_compra');
            }
            if (!Schema::hasColumn('detalle_ventas', 'ganancia_total')) {
                $table->decimal('ganancia_total', 10, 2)->nullable()->after('ganancia_unidad');
            }
        });
    }
};



















