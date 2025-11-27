<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            
            // Período
            $table->string('periodo')->comment('Ej: 2025-11-Q1 (Quincena 1)');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            
            // Asistencia (solo lo esencial)
            $table->integer('dias_laborables')->default(0);
            $table->integer('dias_trabajados')->default(0);
            $table->integer('dias_falta')->default(0);
            $table->integer('minutos_retraso')->default(0);
            
            // Cálculos (solo descuentos)
            $table->decimal('salario_base', 10, 2);
            $table->decimal('total_descuentos_retraso', 10, 2)->default(0);
            $table->decimal('total_descuentos_falta', 10, 2)->default(0);
            $table->decimal('neto_a_pagar', 10, 2)->default(0);
            
            // Estado
            $table->enum('estado', ['Borrador', 'Calculado', 'Aprobado', 'Pagado'])->default('Borrador');
            
            $table->timestamps();
            $table->index(['user_id', 'periodo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};