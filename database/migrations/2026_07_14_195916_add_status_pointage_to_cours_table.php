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
        Schema::table('cours', function (Blueprint $table) {
            // 'ferme' = pas de pointage, 'ouvert' = pointage en cours, 'termine' = cours fini
            $table->string('statut_pointage')->default('ferme'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cours', function (Blueprint $table) {
            // Supprime la colonne en cas de rollback de la migration
            $table->dropColumn('statut_pointage');
        });
    }
};