<?php

namespace App\Http\Controllers;

use App\Models\{Cours, Matiere, Professeur, Classe, Salle};
use Illuminate\Http\Request;

class CoursController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $cours = match ($user->role) {
            'professeur' => Cours::with(['matiere', 'classe', 'salle'])
                ->where('professeur_id', $user->professeur->id)
                ->latest('heureDebut')->paginate(15),
            'etudiant'   => Cours::with(['matiere', 'professeur.user', 'salle'])
                ->where('idClasse', $user->etudiant->idClasse)
                ->latest('heureDebut')->paginate(15),
            default      => Cours::with(['matiere', 'professeur.user', 'classe', 'salle'])
                ->latest('heureDebut')->paginate(15),
        };

        return view('cours.index', compact('cours'));
    }

    public function create()
    {
        $matieres    = Matiere::all();
        $professeurs = Professeur::with('user')->get();
        $classes     = Classe::with(['filiere', 'niveau'])->get();
        $salles      = Salle::all();
        return view('cours.create', compact('matieres', 'professeurs', 'classes', 'salles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'idMatiere'    => 'required|exists:matieres,idMatiere',
            'professeur_id'=> 'required|exists:professeurs,id',
            'idClasse'     => 'required|exists:classes,idClasse',
            'idSalle'      => 'required|exists:salles,idSalle',
            'heureDebut'   => 'required|date',
            'heureFin'     => 'required|date|after:heureDebut',
            'jour'         => 'required|string',
        ]);

        $cours = Cours::create($data);

        // Créer les enregistrements d'absence pour tous les étudiants de la classe
        $etudiants = \App\Models\Etudiant::where('idClasse', $data['idClasse'])->get();
        foreach ($etudiants as $etudiant) {
            \App\Models\Absence::firstOrCreate([
                'etudiant_id' => $etudiant->id,
                'idCours'     => $cours->idCours,
                'date'        => now()->toDateString(),
            ], ['statut' => 'absent']);
        }

        return redirect()->route('cours.index')->with('success', 'Cours créé avec succès.');
    }

    public function show(Cours $cours)
    {
        $cours->load(['matiere', 'professeur.user', 'classe.etudiants.user', 'salle', 'absences.etudiant.user']);
        return view('cours.show', compact('cours'));
    }

    public function edit(Cours $cours)
    {
        $matieres    = Matiere::all();
        $professeurs = Professeur::with('user')->get();
        $classes     = Classe::with(['filiere', 'niveau'])->get();
        $salles      = Salle::all();
        return view('cours.edit', compact('cours', 'matieres', 'professeurs', 'classes', 'salles'));
    }

    public function update(Request $request, Cours $cours)
    {
        $data = $request->validate([
            'idMatiere'    => 'required|exists:matieres,idMatiere',
            'professeur_id'=> 'required|exists:professeurs,id',
            'idClasse'     => 'required|exists:classes,idClasse',
            'idSalle'      => 'required|exists:salles,idSalle',
            'heureDebut'   => 'required|date',
            'heureFin'     => 'required|date|after:heureDebut',
            'jour'         => 'required|string',
            'statut'       => 'required|in:planifie,en_cours,termine,annule',
        ]);

        $cours->update($data);
        return redirect()->route('cours.index')->with('success', 'Cours mis à jour.');
    }

    public function destroy(Cours $cours)
    {
        $cours->delete();
        return redirect()->route('cours.index')->with('success', 'Cours supprimé.');
    }
}