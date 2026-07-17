<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->enum('category',['Drink','Iranian_food','Fast_food','Desert']);
            $table->string('image');
            $table->boolean('is_available');
            $table->unsignedInteger('price');
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->unique(['restaurant_id','name']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
