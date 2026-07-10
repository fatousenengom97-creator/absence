<?php

namespace App\Http\Controllers;

use App\Models\{User, Etudiant, Professeur, Classe, Cours, Absence, Departement, Filiere, Niveau, AnneeScolaire, Salle, Matiere, Inscription};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /* ========== DASHBOARD ========== */
    public function dashboard()
    {
        $stats = [
            'etudiants'      => Etudiant::count(),
            'professeurs'    => Professeur::count(),
            'classes'        => Classe::count(),
            'cours'          => Cours::count(),
            'absences_today' => Absence::whereDate('date', today())->where('statut', 'absent')->count(),
        ];
        return view('admin.dashboard', compact('stats'));
    }

    /* ========== UTILISATEURS (4 COLONNES CORRIGÉES) ========== */
    public function indexUsers()
    {
        // 1. Étudiants avec leur classe via l'historique d'inscription
        $etudiants = User::where('role', 'etudiant')
            ->with('etudiant.inscriptionActuelle.classe')
            ->latest()->paginate(8, ['*'], 'page_etudiants');

        // 2. Professeurs avec leur profil
        $professeurs = User::where('role', 'professeur')
            ->with('professeur')
            ->latest()->paginate(8, ['*'], 'page_professeurs');

        // 3. Administrateurs (Pas de table d'extension, donc pas de ->with)
        $administrateurs = User::where('role', 'administrateur')
            ->latest()->paginate(8, ['*'], 'page_administrateurs');

        // 4. Chefs de Service (Sécurisé : on vérifie si la relation existe sur le modèle User avant de faire un avec/with)
        $chefsServiceQuery = User::where('role', 'chef_service');
        
        if (method_exists(User::class, 'chefService')) {
            $chefsServiceQuery->with('chefService');
        } elseif (method_exists(User::class, 'chef_service')) {
            $chefsServiceQuery->with('chef_service');
        }

        $chefsService = $chefsServiceQuery->latest()->paginate(8, ['*'], 'page_chefs');

        $totalGlobal = User::count();

        return view('admin.users.ListeUtilisateur', compact(
            'etudiants', 'professeurs', 'administrateurs', 'chefsService', 'totalGlobal'
        ));
    }

    public function createUser()
    {
        $departements = Departement::orderBy('nomDep')->get();
        $classes      = Classe::all();
        return view('admin.users.create', compact('departements', 'classes'));
    }

    /* ========== ENREGISTREMENT SÉCURISÉ DE L'UTILISATEUR ========== */
    public function storeUser(Request $request)
    {
        // 1. Validation stricte
        $validatedData = $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6|confirmed',
            'role'      => 'required|in:administrateur,etudiant,professeur,chef_service',
            'telephone' => 'nullable|string|max:20',
            'idClasse'  => 'required_if:role,etudiant', // Requis si c'est un étudiant
        ]);

        // Début de la transaction
        DB::beginTransaction();

        try {
            // 2. On extrait idClasse pour éviter le crash "Unknown column" sur la table users
            $userData = $validatedData;
            unset($userData['idClasse']); 

            $userData['password'] = Hash::make($userData['password']);
            $user = User::create($userData);

            if ($user->role === 'etudiant') {
                
                // Sécurité : On vérifie que la classe existe bien en BDD avant d'aller plus loin
                $classe = Classe::where('idClasse', $request->idClasse)->first();
                if (!$classe) {
                    throw new \Exception("La classe sélectionnée (ID: ".$request->idClasse.") est introuvable dans la base de données.");
                }

                // 3. Création de l'étudiant liée à sa classe
                $etudiant = Etudiant::create([
                    'user_id'       => $user->id,
                    'codePar'       => $request->codePar ?? 'ETU-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                    'dateNaissance' => $request->dateNaissance,
                    'lieuNaissance' => $request->lieuNaissance,
                    'idClasse'      => $request->idClasse,
                ]);

                // 4. Historique d'inscription
                Inscription::create([
                    'etudiant_id' => $etudiant->id,
                    'idClasse'    => $request->idClasse,
                    'idAnnee'     => $classe->idAnnee, 
                ]);
            } 
            elseif ($user->role === 'professeur') {
                Professeur::create([
                    'user_id'    => $user->id,
                    'matricule'  => $request->matricule ?? 'PROF-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                    'specialite' => $request->specialite,
                ]);
            } 
            elseif ($user->role === 'chef_service') {
                \App\Models\ChefService::create([
                    'user_id' => $user->id, 
                    'poste'   => $request->poste
                ]);
            } 
            else {
                // Pour l'administrateur, au cas où une table d'extension existerait
                if (class_exists('\App\Models\Administrateur')) {
                    \App\Models\Administrateur::create([
                        'user_id' => $user->id
                    ]);
                }
            }

            // Tout s'est bien passé
            DB::commit();

            return redirect()->route('admin.users.index')->with('success', 'Utilisateur créé avec succès.');

        } catch (\Exception $e) {
            // En cas de problème, on annule TOUT en base de données
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', "Erreur lors de l'enregistrement : " . $e->getMessage());
        }
    }

    public function editUser(User $user)
    {
        $classes = Classe::all();
        $user->load('etudiant.inscriptionActuelle.classe');
        return view('admin.users.edit', compact('user', 'classes'));
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($user->role === 'etudiant' && $user->etudiant) {
            $user->etudiant->update([
                'codePar'       => $request->codePar ?? $user->etudiant->codePar,
                'dateNaissance' => $request->dateNaissance ?? $user->etudiant->dateNaissance,
                'lieuNaissance' => $request->lieuNaissance ?? $user->etudiant->lieuNaissance,
            ]);

            if ($request->filled('idClasse')) {
                $classe = Classe::where('idClasse', $request->idClasse)->first();

                if ($classe) {
                    Inscription::updateOrCreate(
                        [
                            'etudiant_id' => $user->etudiant->id,
                            'idAnnee'     => $classe->idAnnee,
                        ],
                        [
                            'idClasse' => $request->idClasse,
                        ]
                    );
                }
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur mis à jour avec succès !');
    }

    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Utilisateur supprimé.');
    }

    /* ========== LISTE ÉTUDIANTS (groupés par Filière > Classe) ========== */
    public function indexEtudiants()
    {
        $etudiants = User::where('role', 'etudiant')
            ->with('etudiant.inscriptionActuelle.classe.filiere')
            ->get();

        $etudiantsParFiliere = $etudiants
            ->groupBy(function ($user) {
                return $user->etudiant?->inscriptionActuelle?->classe?->filiere?->nomFiliere ?? 'Sans filière';
            })
            ->map(function ($groupeFiliere) {
                return $groupeFiliere->groupBy(function ($user) {
                    return $user->etudiant?->inscriptionActuelle?->classe?->nom ?? 'Sans classe';
                });
            })
            ->sortKeys();

        $totalEtudiants = $etudiants->count();

        return view('admin.users.etudiants', compact('etudiantsParFiliere', 'totalEtudiants'));
    }

    /* ========== CLASSES (Mis à jour pour inclure tous les critères de sélection) ========== */
    public function indexClasses(Request $request)
    {
        // Récupération de l'ensemble des critères bruts pour alimenter les listes déroulantes de ta vue
        $departements = Departement::orderBy('nomDep')->get();
        $filieres     = Filiere::orderBy('nomFiliere')->get();
        $niveaux      = Niveau::all(); // Récupère Licence 1, 2, 3, Master 1, 2, Doctorat insérés précédemment

        // Construction de la requête avec filtrage dynamique optionnel
        $query = Classe::with(['filiere', 'niveau', 'anneeScolaire']);

        if ($request->filled('idFiliere')) {
            $query->where('idFiliere', $request->idFiliere);
        }
        if ($request->filled('idNiveau')) {
            $query->where('idNiveau', $request->idNiveau);
        }

        $classes = $query->paginate(15);

        return view('admin.classes.index', compact('classes', 'departements', 'filieres', 'niveaux'));
    }

    public function createClasse()
    {
        $filieres = Filiere::all();
        $niveaux  = Niveau::all();
        $annees   = AnneeScolaire::all();
        return view('admin.classes.create', compact('filieres', 'niveaux', 'annees'));
    }

    public function storeClasse(Request $request)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:100',
            'idNiveau'  => 'required|exists:niveaux,idNiveau',
            'idFiliere' => 'required|exists:filieres,idFiliere',
            'idAnnee'   => 'required|exists:annees_scolaires,idAnnee',
            'effectif'  => 'nullable|integer|min:0',
        ]);

        Classe::create($data);
        return redirect()->route('admin.classes.index')->with('success', 'Classe créée.');
    }

    public function destroyClasse(Classe $classe)
    {
        $classe->delete();
        return redirect()->route('admin.classes.index')->with('success', 'Classe supprimée.');
    }

    /* ========== MATIERES (Mis à jour pour inclure tous les critères de sélection) ========== */
    public function indexMatieres(Request $request)
    {
        // Récupération globale pour les dropdowns de sélection de critères
        $departements = Departement::orderBy('nomDep')->get();
        $filieres     = Filiere::orderBy('nomFiliere')->get();
        $niveaux      = Niveau::all();

        // Récupération des matières paginées
        $query = Matiere::query();

        // Si tu as des clés étrangères de filières ou niveaux sur tes matières, tu peux décommenter ces lignes :
        // if ($request->filled('idFiliere')) { $query->where('idFiliere', $request->idFiliere); }
        // if ($request->filled('idNiveau')) { $query->where('idNiveau', $request->idNiveau); }

        $matieres = $query->paginate(15);

        return view('admin.matieres.index', compact('matieres', 'departements', 'filieres', 'niveaux'));
    }

    public function storeMatiere(Request $request)
    {
        $data = $request->validate([
            'nomMatiere'  => 'required|string|max:100',
            'codeUE'      => 'nullable|string|max:20',
            'coefficient' => 'nullable|numeric|min:0',
        ]);

        Matiere::create($data);
        return redirect()->route('admin.matieres.index')->with('success', 'Matière créée.');
    }

    /* ========== SALLES ========== */
    public function indexSalles()
    {
        $salles = Salle::paginate(15);
        return view('admin.salles.index', compact('salles'));
    }

    public function storeSalle(Request $request)
    {
        $request->validate([
            'nom'      => 'required|string|max:100',
            'capacite' => 'nullable|integer|min:0',
        ]);

        Salle::create($request->only('nom', 'capacite'));
        return redirect()->route('admin.salles.index')->with('success', 'Salle créée.');
    }

    /* ========== GESTION DES COURS ========== */
    public function storeCours(Request $request)
{
    $validated = $request->validate([
        'idClasse'      => 'required|exists:classes,idClasse',
        'semestre'      => 'required|string', // Assure-toi que cette colonne existe ou gère-la si besoin
        'jour'          => 'required|string',
        'heureDebut'    => 'required',
        'heureFin'      => 'required',
        'idMatiere'     => 'required', // 🟢 Aligné sur ta migration
        'professeur_id' => 'required|exists:professeurs,id', // 🟢 Aligné sur ta migration
        'idSalle'       => 'required|exists:salles,idSalle', // 🟢 Aligné sur ta migration
    ]);

    // Insertion propre des données validées
    $cours = Cours::create($validated);

    return response()->json([
        'success' => true,
        'message' => 'Cours planifié avec succès !'
    ]);
}

       
    public function indexCours(Request $request)
    {
        $classes = Classe::all();
        $matieres = Matiere::all();
        $professeurs = Professeur::with('user')->get();
        $salles = Salle::all();

        $classeSelectionnee = $request->get('classe_id', $classes->first()?->idClasse);
        $semestreSelectionne = $request->get('semestre', 'S1');

        $cours = Cours::where('idClasse', $classeSelectionnee)
            ->where('semestre', $semestreSelectionne)
            ->with(['matiere', 'professeur.user', 'salle'])
            ->get();

        return view('admin.cours.index', compact(
            'classes', 'matieres', 'professeurs', 'salles', 
            'cours', 'classeSelectionnee', 'semestreSelectionne'
        ));
    }
    public function getMatieresParClasse($classe_id)
{
    // Récupère les matières liées à cette classe (adapte selon tes relations de modèle)
    // Exemple si ton modèle Matiere a une colonne classe_id :
    $matieres = \App\Models\Matiere::where('classe_id', $classe_id)->get();
    
    // Renvoie impérativement une réponse JSON propre
    return response()->json($matieres);
}
}