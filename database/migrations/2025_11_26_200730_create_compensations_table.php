<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compensations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('overtime_id'); // Conexión con tabla overtimes
            $table->decimal('quantity', 5, 2); // Horas a compensar
            $table->timestamps();

            // Clave foránea
            $table->foreign('overtime_id')
                  ->references('id')
                  ->on('overtimes')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compensations');
    }
};