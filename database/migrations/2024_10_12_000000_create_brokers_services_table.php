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
        Schema::create('brokers_services', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ex: "DGI Lubumbashi", "SONAS CTR"
            $table->string('endpoint'); // API URL ex: "https://api.dgi.cd/verify"
            $table->text('api_key')->nullable(); // Bearer/Auth key
            $table->json('doc_types'); // ["vignette", "carte_rose", "controle_technique"]
            $table->boolean('active')->default(true);
            $table->integer('timeout')->default(5); // seconds
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brokers_services');
    }
};
