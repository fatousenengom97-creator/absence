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
        Schema::table('matieres', function (Blueprint $table) {
            // 1. On ajoute la colonne de la clé étrangère (si elle n'existe pas déjà)
            if (!Schema::hasColumn('matieres', 'idUE')) {
                $table->foreignId('idUE')
                      ->nullable() // Permet d'éviter les bugs si une UE est supprimée
                      ->constrained('ues', 'idUE') // Pointe vers la table 'ues' et sa clé primaire 'idUE'
                      ->onDelete('cascade'); // Supprime automatiquement les matières si l'UE est supprimée
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matieres', function (Blueprint $table) {
            // On retire la clé étrangère puis la colonne en cas de retour en arrière
            $table->dropForeign(['idUE']);
            $table->dropColumn('idUE');
        });
    }
};