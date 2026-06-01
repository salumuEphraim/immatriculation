<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('infractions', function (Blueprint $table) {
            if (!Schema::hasColumn('infractions', 'paiement_fournisseur')) {
                $table->string('paiement_fournisseur', 32)->nullable()->after('reference_paiement');
            }
            if (!Schema::hasColumn('infractions', 'paiement_transaction_id')) {
                $table->string('paiement_transaction_id', 191)->nullable()->after('paiement_fournisseur');
            }
            if (!Schema::hasColumn('infractions', 'paiement_statut')) {
                $table->string('paiement_statut', 32)->nullable()->after('paiement_transaction_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('infractions', function (Blueprint $table) {
            foreach (['paiement_fournisseur', 'paiement_transaction_id', 'paiement_statut'] as $col) {
                if (Schema::hasColumn('infractions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
