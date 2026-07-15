<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('seances', function (Blueprint $table) {
            $table->id();
            $table->string('nom_cours');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Le Prof (table users)
            $table->string('salle'); // Ex: "Salle A1", "Amphi SATIC"
            $table->string('classe'); // Ex: "L3 LPRO"
            $table->date('date_seance');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('seances');
    }
};