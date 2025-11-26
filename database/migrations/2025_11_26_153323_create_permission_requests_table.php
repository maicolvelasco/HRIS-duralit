<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_requests', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('user_id', 36);
            $table->char('authorization_id', 36);
            $table->char('titulation_id', 36)->nullable();
            $table->text('motivo');
            $table->enum('tipo', ['dias', 'horas']);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->decimal('cantidad_dias', 5, 2)->nullable();
            $table->decimal('cantidad_horas', 5, 2)->nullable();
            $table->enum('estado', ['Pendiente', 'Aprobado', 'Rechazado', 'Completado'])->default('Pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('authorization_id')->references('id')->on('authorizations')->onDelete('cascade');
            $table->foreign('titulation_id')->references('id')->on('titulations')->onDelete('set null');
            
            // Índices
            $table->index(['user_id', 'estado']);
            $table->index('authorization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_requests');
    }
};