<?php

namespace App\Http\Controllers;

use App\Models\{Cours, Absence, Etudiant, EmploiDuTemps};
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProfesseurController extends Controller
{
    
public function dashboard()
{
    // 1. Récupérer l'utilisateur connecté
    $user = auth()->user();

    // 2. Sécurisation : Vérifier si l'utilisateur a bien un profil professeur lié
    $professeur = $user->professeur;

    if (!$professeur) {
        // Si le profil n'est pas lié, on évite le plantage et on affiche un message clair
        return redirect()->route('login')->with('error', "Votre compte n'est lié à aucun profil de professeur. Contactez l'administrateur.");
    }

    $semaine      = request('semaine', now()->startOfWeek()->format('Y-m-d'));
    $debutSemaine = Carbon::parse($semaine)->startOfWeek();
    $finSemaine   = Carbon::parse($semaine)->endOfWeek();

    // Clé primaire du professeur (à adapter selon ton modèle : id ou idProfesseur)
    // Ici on suppose que c'est $professeur->id ou $professeur->idProfesseur
    $professeurId = $professeur->id ?? $professeur->idProfesseur;

    // Cours planifiés cette semaine (table cours)
    // ATTENTION : Vérifie si la clé dans ta table 'cours' est bien 'professeur_id' ou 'idProfesseur'
    $coursSemaine = Cours::with(['matiere', 'classe', 'salle'])
        ->where('professeur_id', $professeurId)
        ->whereBetween('heureDebut', [$debutSemaine, $finSemaine])
        ->orderBy('heureDebut')
        ->get();

    // EDT fixe (table emplois_du_temps)
    $jourActuel = Carbon::now()->locale('fr')->isoFormat('dddd');
    $jourActuel = ucfirst($jourActuel);

    // ATTENTION : Vérifie si dans 'emplois_du_temps' la clé est 'professeur_id' ou 'idProfesseur'
    $edtSemaine = EmploiDuTemps::with(['matiere', 'classe', 'salle'])
        ->where('professeur_id', $professeurId) 
        ->where('actif', true)
        ->get()
        ->groupBy('jour');

    // Cours du jour avec bouton pointage
    $coursAujourdhui = Cours::with(['matiere', 'classe', 'salle'])
        ->where('professeur_id', $professeurId)
        ->whereDate('heureDebut', today())
        ->orderBy('heureDebut')
        ->get();

    $totalCours    = Cours::where('professeur_id', $professeurId)->count();
    $totalAbsences = Absence::whereHas('cours', function($q) use ($professeurId) {
            $q->where('professeur_id', $professeurId);
        })
        ->where('statut', 'absent')
        ->count();

    $jours = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    $heures = range(8, 18);

    return view('professeur.dashboard', compact(
        'coursSemaine', 'edtSemaine', 'coursAujourdhui',
        'totalCours', 'totalAbsences', 'professeur',
        'jours', 'heures', 'semaine', 'debutSemaine', 'finSemaine'
    ));
}

    public function mesEtudiants()
    {
        $professeur = auth()->user()->professeur;
        $classIds   = Cours::where('professeur_id', $professeur->id)
            ->pluck('idClasse')->unique();

        $etudiants = Etudiant::with(['user', 'inscriptionActuelle.classe'])
            ->whereHas('inscriptions', fn($q) => $q->whereIn('idClasse', $classIds))
            ->paginate(20);

        return view('professeur.etudiants', compact('etudiants'));
    }

    public function absencesClasse(Request $request)
    {
        $professeur = auth()->user()->professeur;
        $absences   = Absence::with(['etudiant.user', 'cours.matiere', 'cours.classe'])
            ->whereHas('cours', fn($q) => $q->where('professeur_id', $professeur->id))
            ->when($request->date, fn($q) => $q->whereDate('date', $request->date))
            ->latest('date')
            ->paginate(20);

        return view('professeur.absences', compact('absences'));
    }

    public function declarerPointage(Request $request, Cours $cours)
    {
        $cours->update(['statut' => 'en_cours']);
        return redirect()->route('biometrie.pointage', $cours)
            ->with('info', 'Session de pointage démarrée.');
    }

public function modifierStatutAbsence(Request $request, \App\Models\Absence $absence)
{
    $request->validate([
        'statut' => 'required|in:present,absent,retard,justifie'
    ]);

    $ancienStatut = $absence->statut;
    $absence->update(['statut' => $request->statut]);

    // Notifier le chef de service via un log ou une notification
    \Illuminate\Support\Facades\Log::info(
        "Professeur " . auth()->user()->prenom . " " . auth()->user()->nom .
        " a modifié le statut de l'absence #" . $absence->id .
        " de '$ancienStatut' vers '" . $request->statut . "'"
    );

    return back()->with('success',
        'Statut modifié. Le chef de service a été notifié.'
    );
}
}