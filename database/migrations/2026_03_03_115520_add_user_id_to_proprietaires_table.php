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
       Schema::table('proprietaires', function (Blueprint $table) {
        // Ajoute la colonne user_id juste après l'ID principal
        // Elle doit être "nullable" au cas où tu as déjà des données anciennes
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->after('id');
      });
    }

      public function down()
   {
      Schema::table('proprietaires', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');
     });
   }
};
