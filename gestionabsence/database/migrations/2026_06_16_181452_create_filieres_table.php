<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // On crée la table filieres SANS la clé étrangère pour l'instant
        Schema::create('filieres', function (Blueprint $table) {
            $table->id('idFiliere');
            $table->string('nomFiliere');
            $table->unsignedBigInteger('idDep'); // Le type exact attendu
            $table->timestamps();
        });

        // On n'ajoute AUCUN alter table ici pour isoler le bug
    }

    public function down(): void
    {
        Schema::dropIfExists('filieres');
    }
};