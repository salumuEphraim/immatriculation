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
            // 1. Ajout du code unique pour le reçu (après montant)
            if (!Schema::hasColumn('infractions', 'code_unique')) {
                $table->string('code_unique')->unique()->after('montant');
            }

            // 2. Ajout du statut pour la validation Admin
            if (!Schema::hasColumn('infractions', 'statut')) {
                $table->string('statut')->default('en_attente')->after('code_unique');
            }

            // 3. Ajout du suivi de paiement (utilisé dans InfractionController)
            if (!Schema::hasColumn('infractions', 'est_payee')) {
                $table->boolean('est_payee')->default(false)->after('statut');
            }

            // 4. Vérification de la colonne date_infraction
            if (!Schema::hasColumn('infractions', 'date_infraction')) {
                $table->timestamp('date_infraction')->nullable()->after('updated_at');
            }

            // 5. Vérification de la colonne agent_id (indispensable pour l'historique)
            if (!Schema::hasColumn('infractions', 'agent_id')) {
                $table->foreignId('agent_id')->constrained('users')->onDelete('cascade')->after('vehicule_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('infractions', function (Blueprint $table) {
            $table->dropColumn(['code_unique', 'statut', 'est_payee', 'date_infraction']);
            // Note: On évite de supprimer agent_id en down pour ne pas casser les contraintes de clés étrangères brutalement
        });
    }
};