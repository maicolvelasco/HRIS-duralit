<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol_shift', function (Blueprint $table) {
            $table->foreignUuid('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignUuid('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->primary(['rol_id', 'shift_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_shift');
    }
};