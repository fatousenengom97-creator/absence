<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id('idPresence');
            $table->foreignId('etudiant_id')->constrained('etudiants')->onDelete('cascade');
            $table->foreignId('idCours')->constrained('cours', 'idCours')->onDelete('cascade');
            $table->date('date');
            $table->enum('statut', ['present', 'absent', 'retard', 'justifie'])->default('absent');
            $table->string('justification')->nullable();
            $table->boolean('pointage_facial')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};