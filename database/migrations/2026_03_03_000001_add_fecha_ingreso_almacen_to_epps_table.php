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
        Schema::table('epps', function (Blueprint $table) {
            if (!Schema::hasColumn('epps', 'fecha_ingreso_almacen')) {
                $table->date('fecha_ingreso_almacen')->nullable()->after('cantidad');
            }
            if (Schema::hasColumn('epps', 'fecha_vencimiento')) {
                $table->date('fecha_vencimiento')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('epps', function (Blueprint $table) {
            if (Schema::hasColumn('epps', 'fecha_ingreso_almacen')) {
                $table->dropColumn('fecha_ingreso_almacen');
            }
        });
    }
};
