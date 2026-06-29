<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annees_scolaires', function (Blueprint $table) {
            $table->id('idAnnee');
            $table->string('libelle'); // ex: 2023-2024
            $table->boolean('active')->default(false);
            $table->timestamps();
        });

        Schema::create('niveaux', function (Blueprint $table) {
            $table->id('idNiveau');
            $table->string('nom'); // L1, L2, L3, M1, M2
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id('idClasse');
            $table->string('nom');
            $table->foreignId('idNiveau')->constrained('niveaux', 'idNiveau')->onDelete('cascade');
            $table->foreignId('idFiliere')->constrained('filieres', 'idFiliere')->onDelete('cascade');
            $table->foreignId('idAnnee')->constrained('annees_scolaires', 'idAnnee')->onDelete('cascade');
            $table->integer('effectif')->default(0);
            $table->timestamps();
        });

        Schema::create('salles', function (Blueprint $table) {
            $table->id('idSalle');
            $table->string('nom');
            $table->integer('capacite')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salles');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('niveaux');
        Schema::dropIfExists('annees_scolaires');
    }
};