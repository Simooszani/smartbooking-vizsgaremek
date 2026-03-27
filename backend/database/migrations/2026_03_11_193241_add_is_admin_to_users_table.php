<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password');
            $table->unsignedBigInteger('managed_hotel_id')->nullable()->after('role');
            $table->foreign('managed_hotel_id')->references('id')->on('hotels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['managed_hotel_id']);
            $table->dropColumn(['role', 'managed_hotel_id']);
        });
    }
};
