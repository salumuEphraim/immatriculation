<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('infractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicule_id')->constrained()->onDelete('cascade'); // Le véhicule fautif
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade'); // L'agent qui signale
            $table->string('type');
            $table->string('lieu');
            $table->decimal('montant', 10, 2);
            $table->string('code_unique')->unique();
            $table->boolean('est_payee')->default(false);
            $table->timestamps();
 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infractions');
    }
};