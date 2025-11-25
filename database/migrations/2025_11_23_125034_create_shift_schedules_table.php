<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shift_id')
                  ->constrained('shifts')
                  ->cascadeOnDelete();
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->json('dias');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_schedules');
    }
};