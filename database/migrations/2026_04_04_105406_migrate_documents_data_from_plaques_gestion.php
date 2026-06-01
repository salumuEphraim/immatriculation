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
        if (Schema::hasTable('plaques')) {
            // Migrate plaques
            DB::table('documents')->insertUsing(
                ['vehicule_id', 'type', 'numero_plaque', 'date_emission', 'date_expiration', 'serie', 'created_at', 'updated_at'],
                DB::table('plaques')->select(
                    'vehicule_id',
                    DB::raw("'plaque'"),
                    'numero_plaque',
                    'date_delivrance',
                    'date_expiration',
                    'serie',
                    DB::raw('NOW()'),
                    DB::raw('NOW()')
                )
            );
        }


        if (Schema::hasTable('gestion_immatriculations')) {
            // Migrate gestion_immatriculations
            DB::table('documents')->insertUsing(
                ['vehicule_id', 'type', 'date_emission', 'date_expiration', 'serie', 'centre_perception', 'created_at', 'updated_at'],
                DB::table('gestion_immatriculations')->select(
                    'vehicule_id',
                    DB::raw("'immatriculation'"),
                    'date_emission',
                    'date_expiration',
                    'serie_plaque',
                    'centre_perception',
                    DB::raw('NOW()'),
                    DB::raw('NOW()')
                )
            );
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('documents')->whereIn('type', ['plaque', 'immatriculation'])->delete();
    }
};
