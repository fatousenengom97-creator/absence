<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapports_cours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idCours');
            $table->boolean('lu')->default(false);
            $table->timestamps();

            $table->foreign('idCours')->references('idCours')->on('cours')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapports_cours');
    }
};