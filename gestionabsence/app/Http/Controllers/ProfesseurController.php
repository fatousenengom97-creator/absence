<?php

namespace App\Http\Controllers;

use App\Models\{Cours, Absence, Etudiant};
use Illuminate\Http\Request;

class ProfesseurController extends Controller
{
    public function dashboard()
    {
        $professeur = auth()->user()->professeur;
        $cours = Cours::with(['matiere', 'classe', 'salle'])
            ->where('professeur_id', $professeur->id)
            ->whereDate('heureDebut', today())
            ->get();

        $totalCours    = Cours::where('professeur_id', $professeur->id)->count();
        $totalAbsences = Absence::whereHas('cours', fn($q) => $q->where('professeur_id', $professeur->id))
            ->where('statut', 'absent')->count();

        return view('professeur.dashboard', compact('cours', 'totalCours', 'totalAbsences', 'professeur'));
    }

    public function mesEtudiants()
    {
        $professeur = auth()->user()->professeur;
        $classIds   = Cours::where('professeur_id', $professeur->id)->pluck('idClasse')->unique();
        $etudiants  = Etudiant::with(['user', 'classe'])->whereIn('idClasse', $classIds)->paginate(20);
        return view('professeur.etudiants', compact('etudiants'));
    }

   public function absencesClasse(Request $request)
{
    // 1. Récupérer le professeur connecté
    $professeur = auth()->user()->professeur;

    // 2. Récupérer les filtres du formulaire (Ta vue utilise name="classe" et name="statut")
    $dateFiltre   = $request->get('date');
    $statutFiltre = $request->get('statut');
    $classeFiltre = $request->get('classe'); // correspond à name="classe" dans ta vue

    // 3. Requête principale des absences
    $absences = Absence::with(['etudiant.user', 'cours.matiere', 'cours.classe'])
        ->whereHas('cours', function ($q) use ($professeur, $classeFiltre) {
            // Filtrer uniquement les cours attribués à ce professeur
            $q->where('professeur_id', $professeur->id);
            
            // Si l'utilisateur filtre par classe
            if (!empty($classeFiltre)) {
                $q->where('idClasse', $classeFiltre);
            }
        })
        // Filtre optionnel par date
        ->when($dateFiltre, function ($q) use ($dateFiltre) {
            $q->whereDate('date', $dateFiltre);
        })
        // Filtre optionnel par statut (présent, absent, retard, justifié)
        ->when($statutFiltre, function ($q) use ($statutFiltre) {
            $q->where('statut', $statutFiltre);
        })
        ->latest('date')
        ->paginate(20);

    // 4. Charger toutes les classes pour le menu déroulant <select name="classe">
    $classes = \App\Models\Classe::all();

    // 5. Envoyer EXACTEMENT les variables attendues par ta vue Blade
    return view('absences.index', compact('absences', 'classes'));
}

    public function declarerPointage(Request $request, Cours $cours)
    {
        $cours->update(['statut' => 'en_cours']);
        return redirect()->route('biometrie.pointage', $cours)->with('info', 'Session de pointage démarrée.');
    }
}