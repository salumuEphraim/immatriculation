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
    Schema::create('vehicules', function (Blueprint $table) {
        $table->id();
        $table->string('plaque')->unique(); // Identifiant pour le contrôle agent
        $table->string('vin')->unique();    // Numéro de châssis
        $table->string('marque');
        $table->string('modele');
        $table->string('couleur');
        
        // Clé étrangère vers la table proprietaires
        $table->foreignId('proprietaire_id')->constrained('proprietaires')->onDelete('cascade');

        // État des documents (pour les alertes à Lubumbashi)
        $table->boolean('has_assurance')->default(false);
        $table->boolean('has_vignette')->default(false);
        $table->boolean('has_controle_technique')->default(false);
        $table->boolean('has_carte_rose')->default(false);
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicules');
    }
};
