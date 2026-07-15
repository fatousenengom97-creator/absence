<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seances', function (Blueprint $table) {
            // Statuts : planifie, en_cours, termine
            $table->string('statut')->default('planifie')->after('heure_fin'); 
            $table->timestamp('heure_demarrage_reel')->nullable()->after('statut');
            $table->timestamp('heure_cloture_reelle')->nullable()->after('heure_demarrage_reel');
        });
    }

    public function down(): void
    {
        Schema::table('seances', function (Blueprint $table) {
            $table->dropColumn(['statut', 'heure_demarrage_reel', 'heure_cloture_reelle']);
        });
    }
};