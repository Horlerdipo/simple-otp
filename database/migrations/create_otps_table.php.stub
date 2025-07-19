<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('simple-otp.table_name'), function (Blueprint $table) {
            $table->id();
            $table->string('destination');
            $table->string('destination_type');
            $table->string('purpose');
            $table->string('token');
            $table->dateTime('expires_at');
            $table->boolean('is_used');
            $table->boolean('is_hashed');
            $table->timestamps();
        });
    }

    public function down(): void
        {
            Schema::dropIfExists(config('simple-otp.table_name'));
        }
};
