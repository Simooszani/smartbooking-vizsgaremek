<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
        {
            Schema::create('hotels', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('address');
                $table->text('description');
                $table->string('image')->nullable();
                $table->decimal('rating', 2, 1)->default(0); // pl: 4.5
                $table->timestamps();
            });

            Schema::create('rooms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
                $table->string('type');
                $table->integer('capacity');
                $table->integer('price_per_night');
                $table->timestamps();
            });

            Schema::create('bookings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained();
                $table->foreignId('room_id')->constrained();
                $table->date('check_in');
                $table->date('check_out');
                $table->integer('guests');
                $table->string('status')->default('confirmed');
                $table->timestamps();
            });

            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained();
                $table->integer('rating');
                $table->text('comment');
                $table->timestamps();
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_system_tables');
    }
};
