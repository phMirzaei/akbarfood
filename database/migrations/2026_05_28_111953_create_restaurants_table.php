<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('permit_scan');
            $table->string('landline_number')->unique();
            $table->string('city');
            $table->string('street');
            $table->string('alley');
            $table->string('management_full_name');
            $table->string('phone')->unique();
            $table->enum('vendor_type',['کافه','رستوران','نانوایی','آبمیوه && بستنی'])->default('رستوران');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
