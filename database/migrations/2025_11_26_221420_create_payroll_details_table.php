<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payroll_id')->constrained('payrolls')->cascadeOnDelete();
            
            // Solo descuentos
            $table->string('concepto'); // "Retraso 15 min", "Falta 1 día"
            $table->integer('cantidad_minutos_dias')->default(0);
            $table->decimal('monto_unitario', 10, 2)->comment('$/minuto o $/día');
            $table->decimal('total', 10, 2);
            $table->string('fuente')->comment('retraso | falta | manual');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};