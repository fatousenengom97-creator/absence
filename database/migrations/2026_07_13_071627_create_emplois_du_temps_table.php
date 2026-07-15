<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emplois_du_temps', function (Blueprint $table) {
            $table->id('idEDT');
            $table->unsignedBigInteger('idClasse');
            $table->unsignedBigInteger('professeur_id');
            $table->unsignedBigInteger('idMatiere');
            $table->unsignedBigInteger('idSalle');
            $table->enum('jour', ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi']);
            $table->time('heureDebut');
            $table->time('heureFin');
            $table->enum('typeCours', ['CM','TD','TP'])->default('CM');
            $table->string('couleur', 7)->default('#3B82F6'); // couleur hex
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->foreign('idClasse')->references('idClasse')->on('classes')->onDelete('cascade');
            $table->foreign('professeur_id')->references('id')->on('professeurs')->onDelete('cascade');
            $table->foreign('idMatiere')->references('idMatiere')->on('matieres')->onDelete('cascade');
            $table->foreign('idSalle')->references('idSalle')->on('salles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emplois_du_temps');
    }
};