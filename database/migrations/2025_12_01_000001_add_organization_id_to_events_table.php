<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'organization_id')) {
                $table->foreignId('organization_id')
                    ->nullable()
                    ->constrained('organizations')
                    ->nullOnDelete()
                    ->after('organizer_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'organization_id')) {
                $table->dropConstrainedForeignId('organization_id');
            }
        });
    }
};

