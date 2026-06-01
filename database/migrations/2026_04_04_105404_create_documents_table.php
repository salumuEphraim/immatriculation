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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicule_id')->constrained('vehicules')->onDelete('cascade');
            $table->enum('type', ['carte_rose', 'vignette', 'permis_conduire', 'controle_technique', 'assurance', 'plaque', 'immatriculation']);
            $table->date('date_emission');
            $table->date('date_expiration')->nullable();
            $table->string('numero_plaque')->nullable();
            $table->string('serie')->nullable();
            $table->string('centre_perception')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
