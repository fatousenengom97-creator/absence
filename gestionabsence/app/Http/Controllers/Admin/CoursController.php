<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cours;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\User;
use App\Models\Salle;

class CoursController extends Controller
{
    /**
     * Affiche l'interface de planification des cours pour l'administrateur.
     */
    public function index(Request $request)
    {
        
// 1. Récupérer toutes les classes pour le menu déroulant
    $classes = Classe::all(); 

    // 2. Accepter 'classe_id' OU 'idClasse' venant de l'URL pour éviter tout conflit
    $classeSelectionnee = $request->get('classe_id') ?? $request->get('idClasse');

    // 3. Charger les cours avec leurs relations
    $query = Cours::with(['matiere', 'professeur', 'salle', 'classe']);
    
    if ($classeSelectionnee) {
        $query->where('idClasse', $classeSelectionnee); 
    }
    
    $cours = $query->get();
        // 4. Charger les données pour remplir les listes déroulantes du Modal d'ajout
        $matieres = Matiere::all();
        $professeurs = User::where('role', 'professeur')->get(); 
        $salles = Salle::all();

        // 5. Renvoyer toutes les données requises à ta vue Blade
        return view('admin.cours.index', compact(
            'classes', 
            'classeSelectionnee', 
            'cours', 
            'matieres', 
            'professeurs', 
            'salles'
        ));
    }

    /**
     * Enregistre un nouveau cours planifié via une requête AJAX / Fetch.
     */
    public function store(Request $request)
{
    // 1. Validation des données reçues
    $validator = \Validator::make($request->all(), [
        'jour'          => 'required|string',
        'heureDebut'    => 'required',
        'heureFin'      => 'required',
        'idClasse'      => 'required',
        'idMatiere'     => 'required',
        'professeur_id' => 'required',
        'salle_id'      => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    try {
        // 2. Récupérer la date du jour sous format Y-m-d (ex: 2026-07-06)
        $dateAujourdhui = date('Y-m-d');

        // 3. Combiner la date avec l'heure reçue pour obtenir un format DATETIME valide (ex: 2026-07-06 16:00:00)
        // On nettoie l'heure pour s'assurer qu'elle ait des secondes au besoin
        $heureDebutComplete = $dateAujourdhui . ' ' . trim($request->heureDebut) . ':00';
        $heureFinComplete   = $dateAujourdhui . ' ' . trim($request->heureFin) . ':00';

        // 4. Insertion propre en Base de Données
     $cours = Cours::create([
    'jour'          => $request->jour,
    'heureDebut'    => $heureDebutComplete,
    'heureFin'      => $heureFinComplete,
    'idClasse'      => $request->idClasse,
    'idMatiere'     => $request->idMatiere,
    'professeur_id' => $request->professeur_id,
    'idSalle'       => $request->salle_id, 
    // 👈 Supprime complètement la ligne du statut pour laisser faire la BDD
]);

        return response()->json([
            'success' => true,
            'message' => 'Cours planifié avec succès !',
            'data'    => $cours
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'errors'  => ['database' => [$e->getMessage()]]
        ], 500);
    }
}

    /**
     * Récupère dynamiquement via AJAX les matières associées à une classe (via ses UEs).
     */
    public function getMatieresParClasse($idClasse)
    {
        // Recherche les matières dont l'UE appartient à la classe ciblée
        $matieres = Matiere::whereHas('ue', function($query) use ($idClasse) {
            // Adapte 'classe_id' si le champ de liaison dans ta table UEs s'appelle 'idClasse'
            $query->where('classe_id', $idClasse); 
        })->get();

        return response()->json($matieres);
    }
}