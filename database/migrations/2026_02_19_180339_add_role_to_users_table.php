<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajoute la colonne 'role' juste après l'email
            $table->enum('role', ['admin', 'agent', 'proprietaire'])
                  ->default('proprietaire')
                  ->after('email');
        });
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprime la colonne 'role' si on revient en arrière
            $table->dropColumn('role');
        });
    }
};
