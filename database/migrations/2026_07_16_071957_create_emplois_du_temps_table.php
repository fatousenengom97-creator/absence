<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emplois_du_temps', function (Blueprint $table) {
            // Clé primaire standard 'id' (évite tous les bugs de génération d'URL)
            $table->id(); 

            // Clés étrangères vers les autres tables
            // Remarque : Si vos tables utilisent des clés primaires personnalisées (ex: idClasse),
            // nous utilisons foreignId() mais pointons explicitement sur ces colonnes.
            $table->foreignId('idClasse')->constrained('classes', 'idClasse')->onDelete('cascade');
            $table->foreignId('idMatiere')->constrained('matieres', 'idMatiere')->onDelete('cascade');
            $table->foreignId('idSalle')->constrained('salles', 'idSalle')->onDelete('cascade');
            
            // Relation avec la table professeurs (qui est généralement liée à la table 'users' ou 'professeurs')
            $table->foreignId('professeur_id')->constrained('professeurs')->onDelete('cascade');

            // Informations du créneau
            $table->string('jour'); // Lundi, Mardi, etc.
            $table->time('heureDebut');
            $table->time('heureFin');
            $table->string('typeCours', 10)->default('CM'); // CM, TD, TP
            $table->string('couleur', 7)->default('#3B82F6'); // Code couleur hexadécimal
            $table->boolean('actif')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emplois_du_temps');
    }
};