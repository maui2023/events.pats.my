<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tier', 20)->default('FREE');
            $table->string('phone', 32)->nullable();
            $table->string('company', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->string('nickname', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};

