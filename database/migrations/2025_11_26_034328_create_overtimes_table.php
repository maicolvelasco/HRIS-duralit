<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtimes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');                // ← nuevo
            $table->date('fecha');
            $table->time('desde');
            $table->time('hasta');
            $table->unsignedInteger('contador');
            $table->text('trabajo');
            $table->string('estado');
            $table->timestamps();

            // Clave foránea
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');  // o `restrict`, según tu política
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};