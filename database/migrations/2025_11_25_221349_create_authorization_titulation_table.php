<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authorization_titulation', function (Blueprint $table) {
            $table->foreignUuid('authorization_id')->constrained('authorizations')->cascadeOnDelete();
            $table->foreignUuid('titulation_id')->constrained('titulations')->cascadeOnDelete();
            $table->primary(['authorization_id', 'titulation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authorization_titulation');
    }
};