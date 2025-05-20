<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('colaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
            // Datos generales
            $table->string('nombre');
            $table->enum('sexo', ['masculino', 'femenino'])->nullable();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->string('ocupacion')->nullable();
            $table->string('area')->nullable();
            $table->decimal('peso', 5, 2)->nullable();      // kg
            $table->decimal('altura', 5, 2)->nullable();   // cm
            // Datos médicos
            $table->string('tipo_sangre')->nullable();
            $table->text('alergias')->nullable();
            $table->text('padecimientos')->nullable();
            $table->decimal('indice_masa_corporal', 5, 2)->nullable();
            // Nivel asignado
            $table->string('nivel_asignado')->default('KoalaFit');
            // Foto (ruta)
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colaborators');
    }
};
