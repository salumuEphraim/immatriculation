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
        Schema::create('bareme_prix', function (Blueprint $table) {
            $table->id();
            $table->string('code_infraction')->unique();
            $table->text('libelle');
            $table->decimal('montant_base', 10, 2);
            $table->decimal('majoration_retard', 10, 2);
            $table->integer('delai_paiement');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bareme_prix');
    }
};
