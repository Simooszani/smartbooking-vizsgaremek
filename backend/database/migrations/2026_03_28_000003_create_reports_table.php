<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reporter_id');
            $table->unsignedBigInteger('reported_user_id');
            $table->unsignedBigInteger('hotel_id');
            $table->string('reason'); // disrespect, inappropriate, profanity, etc.
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, reviewed, warned, dismissed
            $table->timestamps();

            $table->foreign('reporter_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('reported_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('hotel_id')->references('id')->on('hotels')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
