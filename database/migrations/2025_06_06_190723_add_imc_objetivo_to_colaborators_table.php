<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('colaborators', function (Blueprint $table) {
            $table->decimal('IMC_objetivo', 5, 2)->nullable()->after('indice_masa_corporal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colaborators', function (Blueprint $table) {
            $table->dropColumn('IMC_objetivo');
        });
    }
};