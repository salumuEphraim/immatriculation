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
            $table->foreignId('controle_id')->nullable()->constrained('controles')->onDelete('set null');
            $table->foreignId('bareme_prix_id')->nullable()->constrained('bareme_prix')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contraventions', function (Blueprint $table) {
            $table->dropForeign(['controle_id']);
            $table->dropForeign(['bareme_prix_id']);
            $table->dropColumn(['controle_id', 'bareme_prix_id']);
        });
    }
};
