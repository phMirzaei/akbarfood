<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('restuarants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('permit_scan');
            $table->string('landline_number')->unique();
            $table->string('city');
            $table->string('street');
            $table->string('alley');
            $table->string('management_full_name');
            $table->string('management_phone');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('restuarants');
    }
};
