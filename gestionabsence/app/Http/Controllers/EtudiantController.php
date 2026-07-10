<?php

namespace App\Http\Controllers;

use App\Models\{Cours, Absence, Etudiant};
use Illuminate\Http\Request;
use Carbon\Carbon;

class EtudiantController extends Controller
{
    /**
     * Tableau de bord de l'étudiant connecté
     */
    public function dashboard()
    {
        $etudiant = auth()->user()->etudiant;

        // 🛡️ Sécurité : Évite le crash si l'utilisateur connecté n'est pas un étudiant
        if (!$etudiant) {
            return redirect()->back()->with('error', 'Accès refusé : Profil étudiant introuvable.');
        }

        // 🌟 Traduction du jour système en français pour correspondre à la base de données
        $joursEnFrancais = [
            'Monday'    => 'Lundi',
            'Tuesday'   => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday'  => 'Jeudi',
            'Friday'    => 'Vendredi',
            'Saturday'  => 'Samedi',
            'Sunday'    => 'Dimanche'
        ];
        $jourActuel = $joursEnFrancais[Carbon::now()->format('l')];

        // Récupérer uniquement les cours de sa classe prévus pour AUJOURD'HUI
        $cours = Cours::with(['matiere', 'professeur.user', 'salle'])
            ->where('idClasse', $etudiant->idClasse)
            ->where('jour', $jourActuel)
            ->orderBy('heureDebut', 'asc')
            ->get();

        $mesAbsences   = Absence::where('etudiant_id', $etudiant->id)->where('statut', 'absent')->count();
        $mesPresences  = Absence::where('etudiant_id', $etudiant->id)->where('statut', 'present')->count();
        $mesRetards    = Absence::where('etudiant_id', $etudiant->id)->where('statut', 'retard')->count();

        $dernieresAbsences = Absence::with(['cours.matiere'])
            ->where('etudiant_id', $etudiant->id)
            ->latest('date')->take(5)->get();

        return view('etudiant.dashboard', compact(
            'cours', 'etudiant', 'mesAbsences',
            'mesPresences', 'mesRetards', 'dernieresAbsences'
        ));
    }

    /**
     * Liste des absences de l'étudiant avec filtres
     */
    public function mesAbsences(Request $request)
    {
        $etudiant = auth()->user()->etudiant;

        // 🛡️ Sécurité
        if (!$etudiant) {
            return redirect()->back()->with('error', 'Accès refusé : Profil étudiant introuvable.');
        }

        $absences = Absence::with(['cours.matiere', 'cours.professeur.user'])
            ->where('etudiant_id', $etudiant->id)
            ->when($request->statut, fn($q) => $q->where('statut', $request->statut))
            ->when($request->date, fn($q) => $q->whereDate('date', $request->date))
            ->latest('date')->paginate(15);

        return view('etudiant.absences', compact('absences'));
    }

    /**
     * Liste complète des cours (Emploi du temps global de la classe)
     */
    public function mesCours()
    {
        $etudiant = auth()->user()->etudiant;

        // 🛡️ Sécurité
        if (!$etudiant) {
            return redirect()->back()->with('error', 'Accès refusé : Profil étudiant introuvable.');
        }

        // 🌟 Récupération de l'emploi du temps complet trié par ordre logique des jours
        $cours = Cours::with(['matiere', 'professeur.user', 'salle'])
            ->where('idClasse', $etudiant->idClasse)
            ->orderByRaw("FIELD(jour, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche')")
            ->orderBy('heureDebut', 'asc')
            ->paginate(15);

        return view('etudiant.cours', compact('cours'));
    }

    /**
     * Espace Professeur : Liste des étudiants inscrits dans les classes du professeur
     */
    public function mesEtudiants()
    {
        $professeur = auth()->user()->professeur;

        // 🛡️ Sécurité
        if (!$professeur) {
            return redirect()->back()->with('error', 'Accès réservé aux professeurs.');
        }

        $classIds = Cours::where('professeur_id', $professeur->id)->pluck('idClasse')->unique();

        $etudiants = Etudiant::with(['user', 'inscriptionActuelle.classe'])
            ->whereIn('idClasse', $classIds)
            ->paginate(20);

        return view('professeur.etudiants', compact('etudiants'));
    }
}