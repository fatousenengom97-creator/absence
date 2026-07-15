<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id('idInscription');
            $table->unsignedBigInteger('etudiant_id');
            $table->unsignedBigInteger('idClasse');
            $table->unsignedBigInteger('idAnnee');
            $table->timestamps();

            $table->foreign('etudiant_id')->references('id')->on('etudiants')->onDelete('cascade');
            $table->foreign('idClasse')->references('idClasse')->on('classes')->onDelete('cascade');
            $table->foreign('idAnnee')->references('idAnnee')->on('annees_scolaires')->onDelete('cascade');

            // RÈGLE CLÉ : un étudiant ne peut avoir qu'UNE SEULE inscription par année académique
            $table->unique(['etudiant_id', 'idAnnee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};