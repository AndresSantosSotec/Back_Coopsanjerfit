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
        Schema::create('fitcoin_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborator_id')->constrained()->onDelete('cascade');
            $table->integer('balance')->default(0);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fitcoin_accounts');
    }
};
