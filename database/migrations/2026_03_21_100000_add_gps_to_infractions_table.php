<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('infractions', function (Blueprint $table) {
            if (!Schema::hasColumn('infractions', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('lieu');
            }
            if (!Schema::hasColumn('infractions', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('infractions', function (Blueprint $table) {
            foreach (['latitude', 'longitude'] as $col) {
                if (Schema::hasColumn('infractions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
