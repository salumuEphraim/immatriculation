<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('infractions', function (Blueprint $table) {
            if (!Schema::hasColumn('infractions', 'reference_paiement')) {
                $table->string('reference_paiement')->nullable()->after('est_payee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('infractions', function (Blueprint $table) {
            if (Schema::hasColumn('infractions', 'reference_paiement')) {
                $table->dropColumn('reference_paiement');
            }
        });
    }
};
