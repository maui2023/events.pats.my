<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'state_id')) {
                $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete()->after('country');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'state_id')) {
                $table->dropConstrainedForeignId('state_id');
            }
        });
    }
};

