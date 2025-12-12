<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'postcode')) {
                $table->string('postcode', 20)->nullable()->after('location');
            }
            if (!Schema::hasColumn('events', 'country')) {
                $table->string('country', 100)->nullable()->after('postcode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'country')) {
                $table->dropColumn('country');
            }
            if (Schema::hasColumn('events', 'postcode')) {
                $table->dropColumn('postcode');
            }
        });
    }
};

