<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\{
    User, Etudiant, Professeur, Administrateur, ChefService,
    Departement, Filiere, Niveau, AnneeScolaire, Classe,
    Salle, Matiere
};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Désactivation des clés étrangères pour vider les tables sans erreurs de contrainte
        Schema::disableForeignKeyConstraints();

        User::truncate();
        Administrateur::truncate();
        ChefService::truncate();
        Professeur::truncate();
        Etudiant::truncate();
        AnneeScolaire::truncate();
        Niveau::truncate();
        Departement::truncate();
        Filiere::truncate();
        Classe::truncate();
        Salle::truncate();
        Matiere::truncate();

        Schema::enableForeignKeyConstraints();

        // ===== ANNÉE SCOLAIRE =====
        $annee = AnneeScolaire::create(['libelle' => '2025-2026', 'active' => true]);

        // ===== NIVEAUX =====
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

        // ===== DÉPARTEMENTS =====
        $departements = [
            'TIC'  => Departement::create(['nomDep' => 'Technologies de l\'information et de la communication']),
            'TRS'  => Departement::create(['nomDep' => 'Télécommunications, Réseaux et Systèmes']),
            'MASP' => Departement::create(['nomDep' => 'Mathématiques Appliquées / Sciences Physiques']),
        ];

        // ===== FILIÈRES =====
        $filieresData = [
            // Département Technologies de l'information et de la communication
            ['nom' => 'Développement de l\'administration des applications', 'dep' => 'TIC'],
            
            // Département Télécommunications, Réseaux et Systèmes
            ['nom' => 'Système-Réseau-Télécommunication', 'dep' => 'TRS'],
            
            // Département Mathématiques Appliquées / Sciences Physiques
            ['nom' => 'SID', 'dep' => 'MASP'],
            ['nom' => 'Mathématique-physiques-chimie-informatique', 'dep' => 'MASP'],
            ['nom' => 'Physique-chimies', 'dep' => 'MASP'],
            ['nom' => 'Mathematique-Physique-informatique', 'dep' => 'MASP'],
        ];

        $filieres = [];
        foreach ($filieresData as $f) {
            $filieres[$f['nom']] = Filiere::create([
                'nomFiliere' => $f['nom'],
                'idDep'      => $departements[$f['dep']]->idDep,
            ]);
        }

        // ===== GÉNÉRATION DYNAMIQUE DES CLASSES =====
        $classes = [];
        foreach ($filieres as $nomFiliere => $filiere) {
            foreach ($niveaux as $codeNiveau => $niveau) {
                // Restreindre les Masters uniquement aux filières professionnelles principales
                if (in_array($codeNiveau, ['M1', 'M2'])) {
                    if (!in_array($nomFiliere, ['Développement de l\'administration des applications', 'Système-Réseau-Télécommunication', 'SID'])) {
                        continue;
                    }
                }

                $nomClasse = $nomFiliere . ' ' . $niveau->nom;
                $classes[$nomClasse] = Classe::create([
                    'nom'        => $nomClasse,
                    'idNiveau'   => $niveau->idNiveau,
                    'idFiliere'  => $filiere->idFiliere,
                    'idAnnee'    => $annee->idAnnee,
                    'effectif'   => 0,
                ]);
            }
        }

        $this->command->info('✅ Départements, filières et classes réorganisés avec succès.');

        // ===== SALLES =====
        $salles = [];
        $sallesData = [
            ['nom' => 'Salle A101', 'capacite' => 40],
            ['nom' => 'Salle A102', 'capacite' => 40],
            ['nom' => 'Salle B201', 'capacite' => 50],
            ['nom' => 'Amphi SATIC', 'capacite' => 200],
            ['nom' => 'Labo CISCO',  'capacite' => 30],
            ['nom' => 'Labo Info 1', 'capacite' => 30],
            ['nom' => 'Labo Info 2', 'capacite' => 30],
        ];
        foreach ($sallesData as $s) {
            $salles[] = Salle::create($s);
        }

        // ===== MATIÈRES =====
        $matieresData = [
            ['nomMatiere' => 'Algorithmique & Structures de Données', 'codeUE' => 'INF101', 'coefficient' => 3],
            ['nomMatiere' => 'Programmation Orientée Objet (Java/C++)', 'codeUE' => 'INF102', 'coefficient' => 3],
            ['nomMatiere' => 'Architecture des Bases de Données',     'codeUE' => 'INF201', 'coefficient' => 2],
            ['nomMatiere' => 'Réseaux locaux et IP',                  'codeUE' => 'INF202', 'coefficient' => 2],
            ['nomMatiere' => 'Intelligence Artificielle & Big Data',  'codeUE' => 'INF301', 'coefficient' => 3],
            ['nomMatiere' => 'Systèmes d\'exploitation & Unix',        'codeUE' => 'INF203', 'coefficient' => 2],
            ['nomMatiere' => 'Recherche Opérationnelle',              'codeUE' => 'MAT101', 'coefficient' => 2],
            ['nomMatiere' => 'Télécoms et Traitement du Signal',      'codeUE' => 'TEL101', 'coefficient' => 3],
            ['nomMatiere' => 'Physique des semi-conducteurs',         'codeUE' => 'PHY101', 'coefficient' => 2],
            ['nomMatiere' => 'Chimie Organique et Inorganique',       'codeUE' => 'CHM101', 'coefficient' => 2],
        ];
        $matieres = [];
        foreach ($matieresData as $m) {
            $matieres[] = Matiere::create($m);
        }

        // ===== ADMINISTRATEURS =====
        $admin1 = User::create([
            'nom'       => 'Diop',
            'prenom'    => 'Mbaye',
            'telephone' => '784312915',
            'adresse'   => null,
            'email'     => 'djobmbaye@gmail.com',
            'password'  => Hash::make('123456789'),
            'role'      => 'administrateur',
        ]);
        Administrateur::create(['user_id' => $admin1->id, 'niveauAcces' => 'super']);

        $admin2 = User::create([
            'nom'       => 'Ngom',
            'prenom'    => 'Fatou Sene',
            'telephone' => '775144166',
            'adresse'   => 'Tattaguine',
            'email'     => 'fatouSenengom@gmail.com',
            'password'  => Hash::make('123456789'),
            'role'      => 'administrateur',
        ]);
        Administrateur::create(['user_id' => $admin2->id, 'niveauAcces' => 'standard']);

        // ===== CHEF DE SERVICE =====
        $chefUser = User::create([
            'nom'       => 'Ndong',
            'prenom'    => 'Idriss',
            'telephone' => '772345678',
            'adresse'   => null,
            'email'     => 'ndongidriss@gmail.com',
            'password'  => Hash::make('123456789'),
            'role'      => 'chef_service',
        ]);
        ChefService::create(['user_id' => $chefUser->id, 'poste' => 'Chef de Service Scolarité SATIC']);

        // ===== PROFESSEURS =====
        $profsData = [
            ['nom' => 'Deme',   'prenom' => 'Cherif Bachir',   'tel' => '707814372', 'adresse' => null,       'email' => 'demebachir@gmail.com',   'specialite' => 'Base de données'],
            ['nom' => 'Gaye',   'prenom' => 'Mohamed',         'tel' => '763037775', 'adresse' => null,       'email' => 'gayemohamed@gmail.com',  'specialite' => 'Algorithmique et Programmation'],
            ['nom' => 'Ndiaye', 'prenom' => 'Ousmane',         'tel' => '775144166', 'adresse' => 'Paris',    'email' => 'ndiayeousmane@gmail.com','specialite' => 'Laravel'],
            ['nom' => 'Diop',   'prenom' => 'Abdou Khadr',     'tel' => '776275455', 'adresse' => 'Dakar',    'email' => 'diopak@gmail.com',       'specialite' => 'Architecture de la technologie des ordinateurs'],
            ['nom' => 'Ngom',   'prenom' => 'Dierry',          'tel' => '777777777', 'adresse' => 'Diourbel', 'email' => 'ngomdierry@gmail.com',   'specialite' => 'Réseaux'],
            ['nom' => 'Gueye',  'prenom' => 'Abdou Rakhman',   'tel' => '779582206', 'adresse' => 'pikine',   'email' => 'gueyear@gmail.com',      'specialite' => 'Systemes d\'exploitation'],
            ['nom' => 'Baldé',  'prenom' => 'Fatimata',        'tel' => '772563044', 'adresse' => 'Mbour',    'email' => 'baldefatou@gmail.com',   'specialite' => 'Java avancé'],
            ['nom' => 'Kassé',  'prenom' => 'Youssouf',        'tel' => '778888888', 'adresse' => 'Yoff',     'email' => 'kasséyoussouf@gmail.com','specialite' => 'XML'],
        ];

        foreach ($profsData as $index => $p) {
            $user = User::create([
                'nom'       => $p['nom'],
                'prenom'    => $p['prenom'],
                'telephone' => $p['tel'],
                'adresse'   => $p['adresse'],
                'email'     => $p['email'],
                'password'  => Hash::make('123456789'),
                'role'      => 'professeur',
            ]);

            $prof = Professeur::create([
                'user_id'    => $user->id,
                'matricule'  => 'PROF-SATIC-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'specialite' => $p['specialite'],
            ]);

            // Liaison à une matière de manière circulaire
            $prof->matieres()->attach([$matieres[$index % count($matieres)]->idMatiere]);
        }

        // ===== ÉTUDIANTS =====
        $etudiantsData = [
            ['nom' => 'Ndiaye',  'prenom' => 'Bineta',      'tel' => '776669955', 'adresse' => 'Kaolack',    'email' => 'ndiayebineta@gmail.com'],
            ['nom' => 'Diop',    'prenom' => 'Ndeye Astou', 'tel' => '705562231', 'adresse' => 'Thiadiaye',  'email' => 'diopastou@gmail.com'],
            ['nom' => 'Diallo',  'prenom' => 'Coumba',      'tel' => null,        'adresse' => null,         'email' => 'coumba.diallo6@etud.satic.edu'],
            ['nom' => 'Ndiaye',  'prenom' => 'Aminata',     'tel' => null,        'adresse' => null,         'email' => 'aminata.ndiaye7@etud.satic.edu'],
            ['nom' => 'Camara',  'prenom' => 'Rokhaya',     'tel' => null,        'adresse' => null,         'email' => 'rokhaya.camara8@etud.satic.edu'],
            ['nom' => 'Ndiaye',  'prenom' => 'Pape',        'tel' => null,        'adresse' => null,         'email' => 'pape.ndiaye9@etud.satic.edu'],
            ['nom' => 'Faye',    'prenom' => 'Ousmane',     'tel' => null,        'adresse' => null,         'email' => 'ousmane.faye10@etud.satic.edu'],
            ['nom' => 'Camara',  'prenom' => 'Mamadou',     'tel' => null,        'adresse' => null,         'email' => 'mamadou.camara11@etud.satic.edu'],
            ['nom' => 'Faye',    'prenom' => 'Alassane',    'tel' => null,        'adresse' => null,         'email' => 'alassane.faye12@etud.satic.edu'],
            ['nom' => 'Diallo',  'prenom' => 'Ndèye',       'tel' => null,        'adresse' => null,         'email' => 'ndèye.diallo13@etud.satic.edu'],
            ['nom' => 'Faye',    'prenom' => 'Saliou',      'tel' => null,        'adresse' => null,         'email' => 'saliou.faye14@etud.satic.edu'],
            ['nom' => 'Cissé',   'prenom' => 'Aminata',     'tel' => null,        'adresse' => null,         'email' => 'aminata.cissé15@etud.satic.edu'],
            ['nom' => 'Cissé',   'prenom' => 'Awa',         'tel' => null,        'adresse' => null,         'email' => 'awa.cissé16@etud.satic.edu'],
        ];

        // Définition de la classe par défaut
        $classeTest = $classes['Développement de l\'administration des applications Licence 1'];
        $compteurEtu = 1;

        foreach ($etudiantsData as $e) {
            $user = User::create([
                'nom'       => $e['nom'],
                'prenom'    => $e['prenom'],
                'telephone' => $e['tel'],
                'adresse'   => $e['adresse'],
                'email'     => $e['email'],
                'password'  => Hash::make('123456789'),
                'role'      => 'etudiant',
            ]);

            Etudiant::create([
                'user_id'       => $user->id,
                'codePar'       => 'ETU-SATIC-' . str_pad($compteurEtu, 5, '0', STR_PAD_LEFT),
                'idClasse'      => $classeTest->idClasse,
                'dateNaissance' => now()->subYears(rand(18, 24)),
                'lieuNaissance' => 'Bambey',
            ]);

            $compteurEtu++;
        }

        // Mise à jour de l'effectif de la classe cible
        $classeTest->update(['effectif' => count($etudiantsData)]);

        $this->command->info('✅ Seed UFR SATIC mis à jour avec succès !');
    }
}