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
        Schema::table('contraventions', function (Blueprint $table) {
            $table->json('documents_manquants')->nullable()->after('date_infraction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contraventions', function (Blueprint $table) {
            $table->dropColumn('documents_manquants');
        });
    }
};
