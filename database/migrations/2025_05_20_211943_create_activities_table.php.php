<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
{
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->string('exercise_type');
            $table->integer('duration');            // en minutos
            $table->enum('duration_unit', ['minutos','horas'])->default('minutos');
            $table->string('intensity');
            $table->integer('calories');
            $table->integer('steps')->default(0);
            $table->string('selfie_path')->nullable();
            $table->string('device_image_path')->nullable();
            $table->json('attachments')->nullable(); // array de rutas de docs/imágenes extra
            $table->text('notes')->nullable();
            $table->decimal('location_lat', 10, 6)->nullable();
            $table->decimal('location_lng', 10, 6)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activities');
    }
}