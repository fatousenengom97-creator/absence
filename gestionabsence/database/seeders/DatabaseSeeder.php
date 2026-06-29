<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{
    User, Etudiant, Professeur, Administrateur, ChefService,
    Departement, Filiere, Niveau, AnneeScolaire, Classe,
    Salle, Matiere
};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== ANNÉE SCOLAIRE =====
        $annee = AnneeScolaire::create(['libelle' => '2025-2026', 'active' => true]);

        // ===== NIVEAUX (libellés complets) =====
        $niveauxData = [
            'L1' => 'Licence 1',
            'L2' => 'Licence 2',
            'L3' => 'Licence 3',
            'M1' => 'Master 1',
            'M2' => 'Master 2',
        ];
        $niveaux = [];
        foreach ($niveauxData as $code => $libelle) {
            $niveaux[$code] = Niveau::create(['nom' => $libelle]);
        }

        // ===== 3 DÉPARTEMENTS =====
        $departements = [
            'Informatique'        => Departement::create(['nomDep' => 'Informatique']),
            'Télécommunications'  => Departement::create(['nomDep' => 'Télécommunications']),
            'Sciences Appliquées' => Departement::create(['nomDep' => 'Sciences Appliquées']),
        ];

        // ===== 2 FILIÈRES PAR DÉPARTEMENT (noms complets) =====
        $filieresData = [
            ['nom' => 'Génie Logiciel',                             'dep' => 'Informatique'],
            ['nom' => 'Systèmes & Réseaux',                         'dep' => 'Informatique'],
            ['nom' => 'Réseaux & Télécoms',                         'dep' => 'Télécommunications'],
            ['nom' => 'Électronique & Télécoms',                   'dep' => 'Télécommunications'],
            ['nom' => 'Mathématiques-Informatique',                 'dep' => 'Sciences Appliquées'],
            ['nom' => 'Statistiques & Informatique Décisionnelle', 'dep' => 'Sciences Appliquées'],
        ];

        $filieres = [];
        foreach ($filieresData as $f) {
            $filieres[$f['nom']] = Filiere::create([
                'nomFiliere' => $f['nom'],
                'idDep'      => $departements[$f['dep']]->idDep,
            ]);
        }

        // ===== 30 CLASSES avec NOMS COMPLETS =====
        $classes = [];
        foreach ($filieres as $nomFiliere => $filiere) {
            foreach ($niveaux as $codeNiveau => $niveau) {
                $nomClasse = $nomFiliere . ' ' . $niveau->nom;
                $classes[$nomClasse] = Classe::create([
                    'nom'       => $nomClasse,
                    'idNiveau'  => $niveau->idNiveau,
                    'idFiliere' => $filiere->idFiliere,
                    'idAnnee'   => $annee->idAnnee,
                    'effectif'  => 0,
                ]);
            }
        }

        $this->command->info('✅ ' . count($departements) . ' départements créés');
        $this->command->info('✅ ' . count($filieres) . ' filières créées');
        $this->command->info('✅ ' . count($classes) . ' classes créées avec noms complets');

        // ===== SALLES =====
        $salles = [];
        foreach ([
            ['nom' => 'Salle A101', 'capacite' => 40],
            ['nom' => 'Salle A102', 'capacite' => 40],
            ['nom' => 'Salle B201', 'capacite' => 50],
            ['nom' => 'Amphi 1',    'capacite' => 150],
            ['nom' => 'Amphi 2',    'capacite' => 200],
            ['nom' => 'Labo Info 1','capacite' => 30],
            ['nom' => 'Labo Info 2','capacite' => 30],
        ] as $s) {
            $salles[] = Salle::create($s);
        }

        // ===== MATIÈRES =====
        $matieresData = [
            ['nomMatiere' => 'Algorithmique & Structures de Données', 'codeUE' => 'INF101', 'coefficient' => 3],
            ['nomMatiere' => 'Programmation Orientée Objet',          'codeUE' => 'INF102', 'coefficient' => 3],
            ['nomMatiere' => 'Base de Données',                       'codeUE' => 'INF201', 'coefficient' => 2],
            ['nomMatiere' => 'Réseaux Informatiques',                 'codeUE' => 'INF202', 'coefficient' => 2],
            ['nomMatiere' => 'Intelligence Artificielle',             'codeUE' => 'INF301', 'coefficient' => 3],
            ['nomMatiere' => 'Systèmes d\'exploitation',              'codeUE' => 'INF203', 'coefficient' => 2],
            ['nomMatiere' => 'Mathématiques Discrètes',               'codeUE' => 'MAT101', 'coefficient' => 2],
            ['nomMatiere' => 'Télécommunications Numériques',         'codeUE' => 'TEL101', 'coefficient' => 3],
            ['nomMatiere' => 'Électronique Analogique',               'codeUE' => 'ELT101', 'coefficient' => 2],
            ['nomMatiere' => 'Statistiques Appliquées',                'codeUE' => 'STA101', 'coefficient' => 2],
        ];
        $matieres = [];
        foreach ($matieresData as $m) {
            $matieres[] = Matiere::create($m);
        }

        // ===== ADMINISTRATEUR =====
        $adminUser = User::create([
            'nom'       => 'Diallo',
            'prenom'    => 'Amadou',
            'email'     => 'admin@satic.edu',
            'telephone' => '771234567',
            'password'  => Hash::make('password'),
            'role'      => 'administrateur',
        ]);
        Administrateur::create(['user_id' => $adminUser->id, 'niveauAcces' => 'super']);

        // ===== CHEF DE SERVICE =====
        $chefUser = User::create([
            'nom'       => 'Ndiaye',
            'prenom'    => 'Fatou',
            'email'     => 'chef@satic.edu',
            'telephone' => '772345678',
            'password'  => Hash::make('password'),
            'role'      => 'chef_service',
        ]);
        ChefService::create(['user_id' => $chefUser->id, 'poste' => 'Chef Service Pédagogique']);

        // ===== PROFESSEURS =====
        $profsData = [
            ['nom' => 'Mbaye', 'prenom' => 'Ibrahima', 'email' => 'ibrahima.mbaye@satic.edu', 'specialite' => 'Génie Logiciel'],
            ['nom' => 'Sow',   'prenom' => 'Mariama',  'email' => 'mariama.sow@satic.edu',     'specialite' => 'Bases de Données'],
            ['nom' => 'Diop',  'prenom' => 'Ousmane',  'email' => 'ousmane.diop@satic.edu',    'specialite' => 'Réseaux & Télécoms'],
            ['nom' => 'Ba',    'prenom' => 'Aissatou', 'email' => 'aissatou.ba@satic.edu',     'specialite' => 'Intelligence Artificielle'],
            ['nom' => 'Kane',  'prenom' => 'Modou',    'email' => 'modou.kane@satic.edu',      'specialite' => 'Électronique'],
            ['nom' => 'Sy',    'prenom' => 'Aminata',  'email' => 'aminata.sy@satic.edu',      'specialite' => 'Statistiques'],
        ];

        $professeurs = [];
        foreach ($profsData as $i => $p) {
            $user = User::create([
                'nom'      => $p['nom'],
                'prenom'   => $p['prenom'],
                'email'    => $p['email'],
                'password' => Hash::make('password'),
                'role'     => 'professeur',
            ]);
            $prof = Professeur::create([
                'user_id'    => $user->id,
                'matricule'  => 'PROF-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'specialite' => $p['specialite'],
            ]);
            $prof->matieres()->attach([$matieres[$i % count($matieres)]->idMatiere]);
            $professeurs[] = $prof;
        }

        // ===== ÉTUDIANTS — répartis dans CHAQUE classe =====
        $prenomsM = ['Cheikh', 'Moussa', 'Alassane', 'Mamadou', 'Pape', 'Modou', 'Ibrahima', 'Ousmane', 'Saliou', 'Abdou'];
        $prenomsF = ['Rokhaya', 'Ndèye', 'Bineta', 'Sophie', 'Aminata', 'Awa', 'Khady', 'Fatima', 'Coumba', 'Astou'];
        $noms     = ['Fall', 'Diallo', 'Seck', 'Gueye', 'Cissé', 'Sarr', 'Toure', 'Faye', 'Diouf', 'Camara', 'Ndiaye', 'Sow'];

        $compteurGlobal = 1;
        $totalEtudiants = 0;

        foreach ($classes as $nomClasse => $classe) {
            $nbEtudiants = rand(8, 15);

            for ($i = 0; $i < $nbEtudiants; $i++) {
                $estHomme = rand(0, 1) === 1;
                $prenom   = $estHomme ? $prenomsM[array_rand($prenomsM)] : $prenomsF[array_rand($prenomsF)];
                $nom      = $noms[array_rand($noms)];
                $emailSlug = strtolower($prenom . '.' . $nom . $compteurGlobal);

                $user = User::create([
                    'nom'      => $nom,
                    'prenom'   => $prenom,
                    'email'    => $emailSlug . '@etud.satic.edu',
                    'password' => Hash::make('password'),
                    'role'     => 'etudiant',
                ]);

                Etudiant::create([
                    'user_id'       => $user->id,
                    'codePar'       => 'ETU-2025-' . str_pad($compteurGlobal, 5, '0', STR_PAD_LEFT),
                    'idClasse'      => $classe->idClasse,
                    'dateNaissance' => now()->subYears(rand(18, 26))->subDays(rand(0, 365)),
                    'lieuNaissance' => collect(['Dakar', 'Thiès', 'Saint-Louis', 'Ziguinchor', 'Kaolack', 'Touba'])->random(),
                ]);

                $compteurGlobal++;
                $totalEtudiants++;
            }

            $classe->update(['effectif' => $nbEtudiants]);
        }

        $this->command->info("✅ {$totalEtudiants} étudiants créés et répartis dans " . count($classes) . ' classes');

        // Étudiant de démo
        $premiereClasse = $classes['Génie Logiciel Licence 1'];
        $demoUser = User::create([
            'nom'      => 'Demo',
            'prenom'   => 'Etudiant',
            'email'    => 'etudiant@satic.edu',
            'password' => Hash::make('password'),
            'role'     => 'etudiant',
        ]);
        Etudiant::create([
            'user_id'  => $demoUser->id,
            'codePar'  => 'ETU-DEMO-001',
            'idClasse' => $premiereClasse->idClasse,
        ]);
        $premiereClasse->increment('effectif');

        $this->command->info('✅ Seed terminé avec succès !');
        $this->command->table(
            ['Rôle', 'Email', 'Mot de passe'],
            [
                ['Administrateur',   'admin@satic.edu',          'password'],
                ['Chef de service',  'chef@satic.edu',           'password'],
                ['Professeur',       'ibrahima.mbaye@satic.edu', 'password'],
                ['Étudiant (démo)',  'etudiant@satic.edu',       'password'],
            ]
        );
    }
}