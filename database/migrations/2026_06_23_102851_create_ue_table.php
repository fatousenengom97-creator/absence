<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ues', function (Blueprint $table) {
            $table->id('idUE');
            $table->string('codeUE');           // ex: UE-INF1
            $table->string('nomUE');             // ex: "Programmation"
            $table->unsignedBigInteger('idClasse'); // l'UE appartient à une classe précise
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ues');
    }
};