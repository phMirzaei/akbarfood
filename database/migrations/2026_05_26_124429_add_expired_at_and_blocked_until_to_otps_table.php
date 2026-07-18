<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otps', function (Blueprint $table) {
            $table->datetime('expired_at');
            $table->datetime('blocked_until')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('otps', function (Blueprint $table) {
            $table->dropColumn('expired_at');
            $table->dropColumn('blocked_until');
        });
    }
};
