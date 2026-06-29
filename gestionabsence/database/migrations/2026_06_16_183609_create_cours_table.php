<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cours', function (Blueprint $table) {
            $table->id('idCours');
            $table->unsignedBigInteger('idMatiere');
            $table->foreignId('professeur_id')->constrained('professeurs')->onDelete('cascade');
            $table->foreignId('idClasse')->constrained('classes', 'idClasse')->onDelete('cascade');
            $table->foreignId('idSalle')->constrained('salles', 'idSalle')->onDelete('cascade');
            $table->dateTime('heureDebut');
            $table->dateTime('heureFin');
            $table->string('jour');
            $table->enum('statut', ['planifie', 'en_cours', 'termine', 'annule'])->default('planifie');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cours');
    }
};