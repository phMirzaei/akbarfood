<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('phone');
            $table->string('name');
            $table->timestamp('expired_at');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
