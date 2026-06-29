<?php

namespace App\Http\Controllers;

use App\Models\{User, Etudiant, Professeur, Classe, Cours, Absence, Departement, Filiere, Niveau, AnneeScolaire, Salle, Matiere};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    /* ========== UTILISATEURS (4 COLONNES) ========== */
    public function indexUsers()
    {
        $etudiants = User::where('role', 'etudiant')
            ->with('etudiant.inscriptionActuelle.classe')
            ->latest()->paginate(8, ['*'], 'page_etudiants');

        $professeurs = User::where('role', 'professeur')
            ->with('professeur')
            ->latest()->paginate(8, ['*'], 'page_professeurs');

        $administrateurs = User::where('role', 'administrateur')
            ->with('administrateur')
            ->latest()->paginate(8, ['*'], 'page_administrateurs');

        $chefsService = User::where('role', 'chef_service')
            ->with('chefService')
            ->latest()->paginate(8, ['*'], 'page_chefs');

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

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6|confirmed',
            'role'      => 'required|in:administrateur,etudiant,professeur,chef_service',
            'telephone' => 'nullable|string|max:20',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        match ($user->role) {
            'etudiant'     => (function () use ($user, $request) {
                $etudiant = Etudiant::create([
                    'user_id'       => $user->id,
                    'codePar'       => $request->codePar ?? 'ETU-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                    'dateNaissance' => $request->dateNaissance,
                    'lieuNaissance' => $request->lieuNaissance,
                ]);

                if ($request->filled('idClasse')) {
                    $classe = Classe::find($request->idClasse);
                    \App\Models\Inscription::create([
                        'etudiant_id' => $etudiant->id,
                        'idClasse'    => $request->idClasse,
                        'idAnnee'     => $classe->idAnnee,
                    ]);
                }

                return $etudiant;
            })(),
            'professeur'   => Professeur::create([
                'user_id'    => $user->id,
                'matricule'  => $request->matricule ?? 'PROF-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                'specialite' => $request->specialite,
            ]),
            'chef_service' => \App\Models\ChefService::create(['user_id' => $user->id, 'poste' => $request->poste]),
            default        => \App\Models\Administrateur::create(['user_id' => $user->id]),
        };

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur créé avec succès.');
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
                $request->validate(['idClasse' => 'exists:classes,idClasse']);

                $classe = Classe::find($request->idClasse);

                \App\Models\Inscription::updateOrCreate(
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

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur mis à jour avec succès !');
    }

    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Utilisateur supprimé.');
    }

    /* ========== LISTE ÉTUDIANTS ========== */
    /* ========== LISTE ÉTUDIANTS (groupés par Filière > Classe) ========== */
    public function indexEtudiants()
    {
        // IMPORTANT : on charge "inscriptionActuelle.classe.filiere", PAS "etudiant.classe"
        // (cette dernière relation n'existe pas sur le modèle Etudiant).
        $etudiants = User::where('role', 'etudiant')
            ->with('etudiant.inscriptionActuelle.classe.filiere')
            ->get();

        // On groupe en mémoire (après le ->get()) car le groupement se fait sur des
        // relations imbriquées (classe.filiere->nomFiliere), ce qui est plus simple
        // à exprimer avec la collection Laravel qu'avec un groupBy() SQL ici.

        $etudiantsParFiliere = $etudiants
            ->groupBy(function ($user) {
                return $user->etudiant?->inscriptionActuelle?->classe?->filiere?->nomFiliere ?? 'Sans filière';
            })
            ->map(function ($groupeFiliere) {
                // À l'intérieur de chaque filière, on regroupe par classe
                return $groupeFiliere->groupBy(function ($user) {
                    return $user->etudiant?->inscriptionActuelle?->classe?->nom ?? 'Sans classe';
                });
            })
            ->sortKeys(); // ordre alphabétique des filières

        $totalEtudiants = $etudiants->count();

        return view('admin.users.etudiants', compact('etudiantsParFiliere', 'totalEtudiants'));
    }
    /* ========== CLASSES ========== */
    public function indexClasses()
    {
        $classes = Classe::with(['filiere', 'niveau', 'anneeScolaire'])->paginate(15);
        return view('admin.classes.index', compact('classes'));
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

    /* ========== MATIERES ========== */
    public function indexMatieres()
    {
        $matieres = Matiere::paginate(15);
        return view('admin.matieres.index', compact('matieres'));
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
}