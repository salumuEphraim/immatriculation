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
        Schema::create('gestion_immatriculations', function (Blueprint $table) {
         $table->id();
         $table->foreignId('vehicule_id')->constrained()->onDelete('cascade');
         $table->date('date_emission');
         $table->date('date_expiration');
         $table->string('serie_plaque'); // Pour différencier les séries en RDC
         $table->string('centre_perception'); // DGI Lubumbashi, etc.
         $table->timestamps();
     });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gestion_immatriculations');
    }
};
