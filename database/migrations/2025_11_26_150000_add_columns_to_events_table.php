<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'organizer_id')) {
                $table->foreignId('organizer_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('events', 'title')) {
                $table->string('title');
            }
            if (!Schema::hasColumn('events', 'slug')) {
                $table->string('slug')->unique();
            }
            if (!Schema::hasColumn('events', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('events', 'start_at')) {
                $table->dateTime('start_at');
            }
            if (!Schema::hasColumn('events', 'end_at')) {
                $table->dateTime('end_at')->nullable();
            }
            if (!Schema::hasColumn('events', 'location')) {
                $table->string('location')->nullable();
            }
            if (!Schema::hasColumn('events', 'banner_path')) {
                $table->string('banner_path')->nullable();
            }
            if (!Schema::hasColumn('events', 'is_published')) {
                $table->boolean('is_published')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organizer_id');
            $table->dropColumn([
                'title', 'slug', 'description', 'start_at', 'end_at', 'location', 'banner_path', 'is_published',
            ]);
        });
    }
};
