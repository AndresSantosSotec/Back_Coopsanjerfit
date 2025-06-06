<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colaborators', function (Blueprint $table) {
            $table->decimal('peso_objetivo', 5, 2)->nullable()->after('IMC_objetivo');
        });
    }

    public function down(): void
    {
        Schema::table('colaborators', function (Blueprint $table) {
            $table->dropColumn('peso_objetivo');
        });
    }
};