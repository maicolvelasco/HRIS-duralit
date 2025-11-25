<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('nombre')->unique();
            $t->text('descripcion')->nullable();
            $t->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
