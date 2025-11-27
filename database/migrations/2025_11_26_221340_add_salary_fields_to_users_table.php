<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('salario_base', 10, 2)->nullable()->after('foto');
            $table->enum('frecuencia_pago', ['mensual', 'quincenal'])->default('mensual')->after('salario_base');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['salario_base', 'frecuencia_pago']);
        });
    }
};