<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicules', function (Blueprint $table) {
            $table->string('statut_reglementaire', 20)
                ->default('pas_en_regle')
                ->after('proprietaire_id');
        });

        DB::table('vehicules')->update([
            'statut_reglementaire' => 'pas_en_regle',
        ]);
    }

    public function down(): void
    {
        Schema::table('vehicules', function (Blueprint $table) {
            $table->dropColumn('statut_reglementaire');
        });
    }
};
