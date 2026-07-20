<?php

namespace App\Http\Controllers;

use App\Models\{Cours, Absence, Etudiant, EmploiDuTemps};
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProfesseurController extends Controller
{
    
public function dashboard()
{
    $user = auth()->user();
    $professeur = $user->professeur;

    if (!$professeur) {
        return redirect()->route('login')->with('error', "Votre compte n'est lié à aucun profil de professeur. Contactez l'administrateur.");
    }

    $semaine      = request('semaine', now()->startOfWeek()->format('Y-m-d'));
    $debutSemaine = Carbon::parse($semaine)->startOfWeek();
    $finSemaine   = Carbon::parse($semaine)->endOfWeek();

    $professeurId = $professeur->id ?? $professeur->idProfesseur;

    $coursSemaine = Cours::with(['matiere', 'classe', 'salle'])
        ->where('professeur_id', $professeurId)
        ->whereBetween('heureDebut', [$debutSemaine, $finSemaine])
        ->orderBy('heureDebut')
        ->get();

    $jourActuel = Carbon::now()->locale('fr')->isoFormat('dddd');
    $jourActuel = ucfirst($jourActuel);

    $edtSemaine = EmploiDuTemps::with(['matiere', 'classe', 'salle'])
        ->where('professeur_id', $professeurId) 
        ->where('actif', true)
        ->get()
        ->groupBy('jour');

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
public function demarrerDepuisEDT(EmploiDuTemps $edt)
    {
        $professeur = auth()->user()->professeur;

        if ($edt->professeur_id !== $professeur->id) {
            abort(403, 'Ce créneau ne vous appartient pas.');
        }

        $aujourdhui = today()->toDateString();

        // Vérifie si un Cours a déjà été créé aujourd'hui pour ce créneau EDT précis
        $cours = Cours::where('idMatiere', $edt->idMatiere)
            ->where('idClasse', $edt->idClasse)
            ->where('idSalle', $edt->idSalle)
            ->where('professeur_id', $edt->professeur_id)
            ->whereDate('heureDebut', $aujourdhui)
            ->first();

        if (!$cours) {
            $heureDebut = $aujourdhui . ' ' . $edt->heureDebut;
            $heureFin   = $aujourdhui . ' ' . $edt->heureFin;

            $cours = Cours::create([
                'idMatiere'     => $edt->idMatiere,
                'professeur_id' => $edt->professeur_id,
                'idClasse'      => $edt->idClasse,
                'idSalle'       => $edt->idSalle,
                'typeCours'     => $edt->typeCours,
                'heureDebut'    => $heureDebut,
                'heureFin'      => $heureFin,
                'jour'          => $edt->jour,
                'statut'        => 'planifie',
            ]);
        }

        // Réutilise la logique existante de démarrage (statut + initialisation des absences)
        return (new CoursController)->demarrer($cours);
    }

}