<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicules', function (Blueprint $table) {
            $table->renameColumn('plaque', 'plaque_immatriculation');
        });

        Schema::table('vehicules', function (Blueprint $table) {
            $table->dropColumn(['has_vignette', 'has_assurance', 'has_controle_technique', 'has_carte_rose', 'en_regle']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicules', function (Blueprint $table) {
            $table->renameColumn('plaque_immatriculation', 'plaque');
            $table->boolean('has_vignette')->default(false);
            $table->boolean('has_assurance')->default(false);
            $table->boolean('has_controle_technique')->default(false);
            $table->boolean('has_carte_rose')->default(false);
            $table->boolean('en_regle')->default(false);
        });
    }
};
