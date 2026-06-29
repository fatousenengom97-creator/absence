<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donnees_biomettriques', function (Blueprint $table) {
            $table->id('idBiometrie');
            $table->foreignId('etudiant_id')->constrained('etudiants')->onDelete('cascade');
            $table->text('faceVector');
            $table->string('cheminPhoto');
            $table->dateTime('dateEnregistre');
            $table->dateTime('heureEntre')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donnees_biomettriques');
    }
};