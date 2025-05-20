<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            
            // Relación con roles
            $table->foreignId('role_id')
                  ->constrained('roles')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            // Estado y último login
            $table->enum('status', ['Activo','Inactivo'])->default('Activo');
            $table->timestamp('last_login')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
