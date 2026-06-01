<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proprietaires', function (Blueprint $table) {
            $table->string('sexe', 20)->nullable()->after('numero_identite');
            $table->date('date_naissance')->nullable()->after('sexe');
            $table->string('lieu_naissance')->nullable()->after('date_naissance');
            $table->string('nationalite')->nullable()->after('lieu_naissance');
            $table->string('profession')->nullable()->after('nationalite');
            $table->string('commune')->nullable()->after('adresse');
            $table->string('quartier')->nullable()->after('commune');
        });
    }

    public function down(): void
    {
        Schema::table('proprietaires', function (Blueprint $table) {
            $table->dropColumn([
                'sexe',
                'date_naissance',
                'lieu_naissance',
                'nationalite',
                'profession',
                'commune',
                'quartier',
            ]);
        });
    }
};
