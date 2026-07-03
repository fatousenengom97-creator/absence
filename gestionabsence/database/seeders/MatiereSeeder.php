<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Classe;

class MatiereSeeder extends Seeder
{
    public function run()
    {
        // Désactiver temporairement les contraintes de clés étrangères pour nettoyer proprement
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('matieres')->truncate();
        DB::table('ues')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
// 1. Récupération des ID selon les VRAIS noms générés par ton DatabaseSeeder
        $gl_l1 = Classe::where('nom', 'L1 D2A')->first();
        $gl_l2 = Classe::where('nom', 'L2 D2A')->first();
        $gl_l3 = Classe::where('nom', 'L3 D2A')->first();

        $srt_l1 = Classe::where('nom', 'L1 SRT')->first();
        $srt_l2 = Classe::where('nom', 'L2 SRT')->first();
        $srt_l3 = Classe::where('nom', 'L3 SRT')->first();
        // ==========================================
        // MAQUETTE GÉNIE LOGICIEL / D2A
        // ==========================================

        // --- LICENCE 1 D2A ---
        if ($gl_l1) {
            $idClasseL1 = $gl_l1->id ?? $gl_l1->idClasse; // Détection automatique de ta clé primaire
            
            // UE Architecture et Système
            $ue1 = DB::table('ues')->insertGetId([
                'codeUE' => 'WEB111',
                'nomUE' => 'Architecture et Système d\'exploitation',
                'idClasse' => $idClasseL1,
                'created_at' => now(), 'updated_at' => now()
            ]);
            DB::table('matieres')->insert([
                ['codeMatiere' => 'WEB1111', 'nomMatiere' => 'Architecture et technologie des ordinateurs', 'cm' => 24, 'td' => 24, 'tp' => 24, 'coefficient' => 1, 'idUE' => $ue1, 'created_at' => now(), 'updated_at' => now()],
                ['codeMatiere' => 'WEB1112', 'nomMatiere' => 'Système d\'exploitation', 'cm' => 24, 'td' => 24, 'tp' => 0, 'coefficient' => 1, 'idUE' => $ue1, 'created_at' => now(), 'updated_at' => now()],
            ]);

            // UE Algorithmique
            $ue2 = DB::table('ues')->insertGetId([
                'codeUE' => 'WEB114',
                'nomUE' => 'Algorithmique',
                'idClasse' => $idClasseL1,
                'created_at' => now(), 'updated_at' => now()
            ]);
            DB::table('matieres')->insert([
                ['codeMatiere' => 'WEB1141', 'nomMatiere' => 'Introduction à l\'algorithmique et à la programmation', 'cm' => 36, 'td' => 36, 'tp' => 24, 'coefficient' => 1, 'idUE' => $ue2, 'created_at' => now(), 'updated_at' => now()],
            ]);

            // UE Web Fondements
            $ue3 = DB::table('ues')->insertGetId([
                'codeUE' => 'WEB121',
                'nomUE' => 'Algorithmique et programmation',
                'idClasse' => $idClasseL1,
                'created_at' => now(), 'updated_at' => now()
            ]);
            DB::table('matieres')->insert([
                ['codeMatiere' => 'WEB1211', 'nomMatiere' => 'HTML et CSS', 'cm' => 24, 'td' => 36, 'tp' => 48, 'coefficient' => 1, 'idUE' => $ue3, 'created_at' => now(), 'updated_at' => now()],
                ['codeMatiere' => 'WEB1212', 'nomMatiere' => 'Approfondissement à l\'algorithmique', 'cm' => 36, 'td' => 24, 'tp' => 24, 'coefficient' => 1, 'idUE' => $ue3, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // --- LICENCE 2 D2A ---
        if ($gl_l2) {
            $idClasseL2 = $gl_l2->id ?? $gl_l2->idClasse;
            
            // UE Conception
            $ueConcep = DB::table('ues')->insertGetId([
                'codeUE' => 'WEB232',
                'nomUE' => 'Conception des systèmes d\'information',
                'idClasse' => $idClasseL2,
                'created_at' => now(), 'updated_at' => now()
            ]);
            DB::table('matieres')->insert([
                ['codeMatiere' => 'WEB2321', 'nomMatiere' => 'Conception des systèmes d\'information avec Merise', 'cm' => 36, 'td' => 24, 'tp' => 24, 'coefficient' => 1, 'idUE' => $ueConcep, 'created_at' => now(), 'updated_at' => now()],
            ]);

            // UE Web Dynamique
            $ueWebDyn = DB::table('ues')->insertGetId([
                'codeUE' => 'WEB241',
                'nomUE' => 'Programmation Web dynamique / orientée objet',
                'idClasse' => $idClasseL2,
                'created_at' => now(), 'updated_at' => now()
            ]);
            DB::table('matieres')->insert([
                ['codeMatiere' => 'WEB2411', 'nomMatiere' => 'PHP', 'cm' => 36, 'td' => 48, 'tp' => 56, 'coefficient' => 1, 'idUE' => $ueWebDyn, 'created_at' => now(), 'updated_at' => now()],
                ['codeMatiere' => 'WEB2412', 'nomMatiere' => 'POO avec Java', 'cm' => 36, 'td' => 24, 'tp' => 24, 'coefficient' => 1, 'idUE' => $ueWebDyn, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // --- LICENCE 3 D2A ---
        if ($gl_l3) {
            $idClasseL3 = $gl_l3->id ?? $gl_l3->idClasse;
            
            // UE Frameworks
            $ueFramework = DB::table('ues')->insertGetId([
                'codeUE' => 'WEB352',
                'nomUE' => 'PHP, JSP et Servlet',
                'idClasse' => $idClasseL3,
                'created_at' => now(), 'updated_at' => now()
            ]);
            DB::table('matieres')->insert([
                ['codeMatiere' => 'WEB3521', 'nomMatiere' => 'Frameworks Web (Laravel / React)', 'cm' => 24, 'td' => 12, 'tp' => 48, 'coefficient' => 1, 'idUE' => $ueFramework, 'created_at' => now(), 'updated_at' => now()],
                ['codeMatiere' => 'WEB3522', 'nomMatiere' => 'JSP et Servlet', 'cm' => 24, 'td' => 24, 'tp' => 24, 'coefficient' => 1, 'idUE' => $ueFramework, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // ==========================================
        // MAQUETTE SYSTÈMES & RÉSEAUX (SRT)
        // ==========================================

        // --- LICENCE 1 SRT ---
        if ($srt_l1) {
            $idClasseS1 = $srt_l1->id ?? $srt_l1->idClasse;
            
            $ueSrt1 = DB::table('ues')->insertGetId([
                'codeUE' => 'SRT121',
                'nomUE' => 'Réseaux et Structure de données',
                'idClasse' => $idClasseS1,
                'created_at' => now(), 'updated_at' => now()
            ]);
            DB::table('matieres')->insert([
                ['codeMatiere' => 'SRT1211', 'nomMatiere' => 'Introduction aux Réseaux', 'cm' => 24, 'td' => 24, 'tp' => 12, 'coefficient' => 2, 'idUE' => $ueSrt1, 'created_at' => now(), 'updated_at' => now()],
                ['codeMatiere' => 'SRT1212', 'nomMatiere' => 'Structure de données', 'cm' => 36, 'td' => 24, 'tp' => 12, 'coefficient' => 1, 'idUE' => $ueSrt1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // --- LICENCE 2 SRT ---
        if ($srt_l2) {
            $idClasseS2 = $srt_l2->id ?? $srt_l2->idClasse;
            
            $ueSrt2 = DB::table('ues')->insertGetId([
                'codeUE' => 'SRT243',
                'nomUE' => 'Administration Système',
                'idClasse' => $idClasseS2,
                'created_at' => now(), 'updated_at' => now()
            ]);
            DB::table('matieres')->insert([
                ['codeMatiere' => 'SRT2431', 'nomMatiere' => 'Administration Linux', 'cm' => 24, 'td' => 0, 'tp' => 24, 'coefficient' => 1, 'idUE' => $ueSrt2, 'created_at' => now(), 'updated_at' => now()],
                ['codeMatiere' => 'SRT2432', 'nomMatiere' => 'Administration Windows', 'cm' => 24, 'td' => 0, 'tp' => 24, 'coefficient' => 1, 'idUE' => $ueSrt2, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // --- LICENCE 3 SRT ---
        if ($srt_l3) {
            $idClasseS3 = $srt_l3->id ?? $srt_l3->idClasse;
            
            $ueSrt3 = DB::table('ues')->insertGetId([
                'codeUE' => 'SRT351',
                'nomUE' => 'Réseaux informatiques',
                'idClasse' => $idClasseS3,
                'created_at' => now(), 'updated_at' => now()
            ]);
            DB::table('matieres')->insert([
                ['codeMatiere' => 'SRT3511', 'nomMatiere' => 'Routage', 'cm' => 24, 'td' => 12, 'tp' => 24, 'coefficient' => 1, 'idUE' => $ueSrt3, 'created_at' => now(), 'updated_at' => now()],
                ['codeMatiere' => 'SRT3512', 'nomMatiere' => 'Réseaux étendus', 'cm' => 12, 'td' => 0, 'tp' => 24, 'coefficient' => 1, 'idUE' => $ueSrt3, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        echo "✅ UEs et Matières injectées avec succès pour les filières D2A et SRT !";
    }
}