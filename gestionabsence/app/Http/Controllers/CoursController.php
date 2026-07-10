<?php

namespace App\Http\Controllers;

use App\Models\{Cours, Matiere, Professeur, Classe, Salle};
use Illuminate\Http\Request;

class CoursController extends Controller
{
   public function index(Request $request)
{
    $user = auth()->user();

    // 1. Récupérer les filtres depuis l'URL
    $classeSelectionnee = $request->get('classe_id') ?? $request->get('idClasse');
    $semestreSelectionne = $request->get('semestre');

    // 2. Préparer la requête : Le prof ne voit QUE ses propres cours
    $coursQuery = Cours::with(['matiere', 'salle', 'classe'])
        ->where('professeur_id', $user->professeur->id ?? null);

    // Filtre par classe (qui fonctionne en BDD)
    if ($classeSelectionnee) {
        $coursQuery->where('idClasse', $classeSelectionnee);
    }

    // ❌ ON RETIRE OU ON COMMENTE CETTE LIGNE QUI FAIT CRASHER LA BDD :
    // if ($semestreSelectionne) {
    //     $coursQuery->where('semestre', $semestreSelectionne);
    // }

    // 3. Récupérer les cours triés
    $cours = $coursQuery->latest('heureDebut')->get();

    // 4. Charger les classes depuis ta vraie BDD (qui marche chez l'admin)
    $classes = Classe::all(); 

    return view('cours.index', compact(
        'cours', 
        'classes', 
        'classeSelectionnee', 
        'semestreSelectionne'
    ));
}


    public function store(Request $request)
    {
    // Adapte les clés ici selon le 'name' exact de ton HTML !
    $data = $request->validate([
        'idMatiere'     => 'required|exists:matieres,idMatiere', // ou 'matiere_id'
        'professeur_id' => 'required|exists:professeurs,id',
        'idClasse'      => 'required|exists:classes,idClasse',
        'salle_id'      => 'required|exists:salles,id',         // ou 'idSalle'
        'heureDebut'    => 'required|date',
        'heureFin'      => 'required|date|after:heureDebut',
        'jour'          => 'required|string',
        'semestre'      => 'nullable|string',
    ]);

    // Création du cours
    $cours = Cours::create($data);

    // Génération automatique des fiches d'absence (statut 'présent' ou 'non marqué' par défaut)
    $etudiants = \App\Models\Etudiant::where('idClasse', $data['idClasse'])->get();
    
    foreach ($etudiants as $etudiant) {
        \App\Models\Absence::firstOrCreate([
            'etudiant_id' => $etudiant->id,
            'idCours'     => $cours->idCours ?? $cours->id,
            'date'        => now()->toDateString(),
        ], [
            'statut'      => 'non_marque' // Mieux vaut 'non_marque' que 'absent' par défaut avant le cours !
        ]);
    }

    return redirect()->route('cours.index')->with('success', 'Le cours a été planifié avec succès.');
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
    public function getMatieresParClasse($classe_id)
    {
        // Récupère les matières liées à la classe
        $matieres = \App\Models\Matiere::where('idClasse', $classe_id)
            ->orWhere('classe_id', $classe_id) // selon le nom de ta colonne
            ->get();

        return response()->json($matieres);
    }
}