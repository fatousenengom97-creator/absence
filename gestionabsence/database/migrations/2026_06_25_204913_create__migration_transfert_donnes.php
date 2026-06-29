<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Transférer chaque étudiant existant (avec son idClasse actuel)
        // vers la table inscriptions, en utilisant l'année académique active.
        $anneeActive = DB::table('annees_scolaires')->where('active', true)->first();

        if (!$anneeActive) {
            return; // Rien à faire si aucune année active n'existe
        }

        $etudiants = DB::table('etudiants')->whereNotNull('idClasse')->get();

        foreach ($etudiants as $etudiant) {
            DB::table('inscriptions')->insert([
                'etudiant_id' => $etudiant->id,
                'idClasse'    => $etudiant->idClasse,
                'idAnnee'     => $anneeActive->idAnnee,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('inscriptions')->truncate();
    }
};