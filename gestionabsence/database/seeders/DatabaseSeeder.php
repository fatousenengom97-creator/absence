<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Désactivation des contraintes de clé étrangère pour vider les tables proprement
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('classes')->truncate();
        DB::table('filieres')->truncate();
        DB::table('departements')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Insertion dans 'departements'
        $depts = [
            'TIC' => DB::table('departements')->insertGetId(['nomDep' => "Technologie de l'Information et de la Communication", 'created_at' => now(), 'updated_at' => now()]),
            'MATH' => DB::table('departements')->insertGetId(['nomDep' => 'Mathématiques', 'created_at' => now(), 'updated_at' => now()]),
            'CHIMIE' => DB::table('departements')->insertGetId(['nomDep' => 'Chimie', 'created_at' => now(), 'updated_at' => now()]),
            'PHYSIQUE' => DB::table('departements')->insertGetId(['nomDep' => 'Physique', 'created_at' => now(), 'updated_at' => now()]),
        ];

        // 3. Configuration des Filières officielles de l'UFR SATIC
        $filieresData = [
            // --- Département TIC ---
            ['nom' => 'Développement et Administration des Applications (D2A)', 'code' => 'D2A', 'dept' => 'TIC'],
            ['nom' => 'Système Réseaux et Télécommunication (SRT)', 'code' => 'SRT', 'dept' => 'TIC'],
            ['nom' => 'Licence Professionnelle en Création MultiMedia (LPCM)', 'code' => 'LPCM', 'dept' => 'TIC'],
            ['nom' => "Master Système d'Informations (SI)", 'code' => 'SI', 'dept' => 'TIC'],
            ['nom' => 'Master Systèmes et Réseaux (SR)', 'code' => 'SR', 'dept' => 'TIC'],

            // --- Tronc Commun et Autres Départements ---
            ['nom' => 'Mathématiques Physique Chimie Informatique (MPCI)', 'code' => 'MPCI', 'dept' => 'MATH'],
            ['nom' => 'Mathématiques-Physique-Informatique (MPI)', 'code' => 'MPI', 'dept' => 'MATH'],
            ['nom' => 'Physique-Chimie (PC)', 'code' => 'PC', 'dept' => 'PHYSIQUE'],
            ['nom' => 'Statistiques et Informatiques Décisionnelles (SID)', 'code' => 'SID', 'dept' => 'MATH'],
            ['nom' => 'Mathématiques et Applications (MMA)', 'code' => 'MMA', 'dept' => 'MATH'],
            ['nom' => 'Chimie', 'code' => 'CHIMIE', 'dept' => 'CHIMIE'],
            ['nom' => 'Physique Médicale', 'code' => 'PM', 'dept' => 'PHYSIQUE'],
        ];

        // 4. Insertion dans 'filieres'
        $filiereIds = [];
        foreach ($filieresData as $f) {
            $filiereIds[$f['code']] = DB::table('filieres')->insertGetId([
                'nomFiliere'     => $f['nom'],
                'idDep'          => $depts[$f['dept']],
                'created_at'     => now(),
                'updated_at'     => now()
            ]);
        }

        // 5. Configuration précise de toutes les classes demandées
        $classes = [
            // --- FILIÈRE D2A ---
            ['nom' => 'L1 D2A', 'filiere' => 'D2A', 'niveau' => 'Licence 1'],
            ['nom' => 'L2 D2A', 'filiere' => 'D2A', 'niveau' => 'Licence 2'],
            ['nom' => 'L3 D2A', 'filiere' => 'D2A', 'niveau' => 'Licence 3'],
            ['nom' => "MASTER 1 S I (Systeme d'information)", 'filiere' => 'SI', 'niveau' => 'Master 1'],
            ['nom' => "MASTER 2 S I (Systeme d'information)", 'filiere' => 'SI', 'niveau' => 'Master 2'],

            // --- FILIÈRE SRT ---
            ['nom' => 'L1 SRT', 'filiere' => 'SRT', 'niveau' => 'Licence 1'],
            ['nom' => 'L2 SRT', 'filiere' => 'SRT', 'niveau' => 'Licence 2'],
            ['nom' => 'L3 SRT', 'filiere' => 'SRT', 'niveau' => 'Licence 3'],
            ['nom' => 'MASTER 1 S R (Systeme Reseaux)', 'filiere' => 'SR', 'niveau' => 'Master 1'],
            ['nom' => 'MASTER 2 S R (Systeme Reseaux)', 'filiere' => 'SR', 'niveau' => 'Master 2'],

            // --- CRÉATION MULTIMÉDIA ---
            ['nom' => 'L1 LPCM', 'filiere' => 'LPCM', 'niveau' => 'Licence 1'],
            ['nom' => 'L2 LPCM', 'filiere' => 'LPCM', 'niveau' => 'Licence 2'],
            ['nom' => 'L3 LPCM', 'filiere' => 'LPCM', 'niveau' => 'Licence 3'],

            // --- MPCI, MPI, PC, SID ---
            ['nom' => 'L1 MPCI', 'filiere' => 'MPCI', 'niveau' => 'Licence 1'],
            ['nom' => 'L2 MPI', 'filiere' => 'MPI', 'niveau' => 'Licence 2'],
            ['nom' => 'L3 MPI', 'filiere' => 'MPI', 'niveau' => 'Licence 3'],
            ['nom' => 'L2 PC', 'filiere' => 'PC', 'niveau' => 'Licence 2'],
            ['nom' => 'L3 PC', 'filiere' => 'PC', 'niveau' => 'Licence 3'],
            ['nom' => 'L2 SID', 'filiere' => 'SID', 'niveau' => 'Licence 2'],
            ['nom' => 'L3 SID', 'filiere' => 'SID', 'niveau' => 'Licence 3'],
            ['nom' => 'Master SID', 'filiere' => 'SID', 'niveau' => 'Master'],

            // --- AUTRES MASTERS ---
            ['nom' => 'Master Chimie', 'filiere' => 'CHIMIE', 'niveau' => 'Master'],
            ['nom' => 'Master Physique Médicale', 'filiere' => 'PM', 'niveau' => 'Master'],
            ['nom' => 'Master Mathématiques et Applications (MMA)', 'filiere' => 'MMA', 'niveau' => 'Master'],
        ];

        // 6. Insertion dans 'classes' (Correction : 'nomClasse' supprimé)
        foreach ($classes as $c) {
            $idNiveauMap = [
                'Licence 1' => 1,
                'Licence 2' => 2,
                'Licence 3' => 3,
                'Master 1'  => 4,
                'Master 2'  => 5,
                'Master'    => 6,
            ];

            $idNiveau = $idNiveauMap[$c['niveau']] ?? 1;

            DB::table('classes')->insert([
                'nom'        => $c['nom'], 
                'idFiliere'  => $filiereIds[$c['filiere']],
                'idNiveau'   => $idNiveau,
                'idAnnee'    => 1, 
                'effectif'   => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        // ==========================================
        // CRÉATION DU COMPTE ADMINISTRATEUR PAR DÉFAUT
        // ==========================================
        
        $userId = DB::table('users')->insertGetId([
            'prenom'     => 'Amadou',           // Aligné avec tes logs récents
            'nom'        => 'Diallo',           // Aligné avec tes logs récents
            'email'      => 'admin@satic.edu',
            'password'   => bcrypt('password'), // Le mot de passe sera 'password'
            'role'       => 'administrateur',   // Ton rôle exact dans l'application
            'is_active'  => true,               // Sécurité d'activation vue dans ton diagnostic
            'created_at' => now(),
            'updated_at' => now()
        ]);
        

        // 7. Appel du Seeder des matières officielles
        $this->call(MatiereSeeder::class);
    }
}