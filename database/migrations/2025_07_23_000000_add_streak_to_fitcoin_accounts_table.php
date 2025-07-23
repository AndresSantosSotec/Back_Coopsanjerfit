<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fitcoin_accounts', function (Blueprint $table) {
            $table->unsignedInteger('streak_count')->default(0);
            $table->date('last_activity_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('fitcoin_accounts', function (Blueprint $table) {
            $table->dropColumn(['streak_count', 'last_activity_date']);
        });
    }
};
