<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('matieres', function (Blueprint $table) {
    $table->id('idMatiere');
    $table->string('codeMatiere')->unique();
    $table->string('nomMatiere');
    $table->integer('cm')->default(0);
    $table->integer('td')->default(0);
    $table->integer('tp')->default(0);
    $table->integer('coefficient')->default(1);
    $table->unsignedBigInteger('idUE');
    $table->foreign('idUE')->references('idUE')->on('ues')->onDelete('cascade');
    $table->timestamps();
});

        Schema::create('professeur_matiere', function (Blueprint $table) {
            $table->unsignedBigInteger('professeur_id');
            $table->unsignedBigInteger('idMatiere');

            $table->foreign('professeur_id')
                ->references('id')
                ->on('professeurs')
                ->onDelete('cascade');

            $table->foreign('idMatiere')
                ->references('idMatiere')
                ->on('matieres')
                ->onDelete('cascade');

            $table->primary(['professeur_id', 'idMatiere']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professeur_matiere');
        Schema::dropIfExists('matieres');
    }
};