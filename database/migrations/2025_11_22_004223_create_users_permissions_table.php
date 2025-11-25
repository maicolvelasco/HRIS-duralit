<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_permissions', function (Blueprint $t) {
            $t->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignUuid('permission_id')->constrained('permissions')->cascadeOnDelete();
            $t->boolean('granted')->default(true); // true=otorgado, false=negado
            $t->primary(['user_id','permission_id']);
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('users_permissions');
    }
};
