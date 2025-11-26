<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL ≥ 8.0 / MariaDB ≥ 10.4
        DB::statement("ALTER TABLE overtimes MODIFY estado ENUM('Pendiente','Aprobado','Usado') NOT NULL");
    }

    public function down(): void
    {
        // Volver a varchar por si haces rollback
        DB::statement("ALTER TABLE overtimes MODIFY estado VARCHAR(255) NOT NULL");
    }
};