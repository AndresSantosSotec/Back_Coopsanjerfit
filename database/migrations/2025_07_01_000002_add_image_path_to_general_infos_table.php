<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_infos', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('general_infos', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
