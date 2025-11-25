<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles_permissions', function (Blueprint $t) {
            $t->foreignUuid('rol_id')->constrained('roles')->cascadeOnDelete();
            $t->foreignUuid('permission_id')->constrained('permissions')->cascadeOnDelete();
            $t->primary(['rol_id','permission_id']);
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('roles_permissions');
    }
};
