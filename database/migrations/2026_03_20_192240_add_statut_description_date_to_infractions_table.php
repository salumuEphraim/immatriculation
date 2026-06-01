<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
    {
        Schema::table('infractions', function (Blueprint $table) {
            if (!Schema::hasColumn('infractions', 'statut')) {
                $table->string('statut')->default('en_attente');
            }
            if (!Schema::hasColumn('infractions', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('infractions', 'date_infraction')) {
                $table->dateTime('date_infraction');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
public function down(): void
    {
        Schema::table('infractions', function (Blueprint $table) {
            $table->dropColumn(['statut', 'description', 'date_infraction']);
        });
    }
};
