<?php

namespace App\Http\Controllers;

use App\Models\{Cours, Absence};
use Illuminate\Http\Request;

class EtudiantController extends Controller
{
    public function dashboard()
    {
        $etudiant = auth()->user()->etudiant;

        $cours = Cours::with(['matiere', 'professeur.user', 'salle'])
            ->where('idClasse', $etudiant->idClasse)
            ->whereDate('heureDebut', today())
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

    public function mesAbsences(Request $request)
    {
        $etudiant = auth()->user()->etudiant;
        $absences = Absence::with(['cours.matiere', 'cours.professeur.user'])
            ->where('etudiant_id', $etudiant->id)
            ->when($request->statut, fn($q) => $q->where('statut', $request->statut))
            ->when($request->date, fn($q) => $q->whereDate('date', $request->date))
            ->latest('date')->paginate(15);

        return view('etudiant.absences', compact('absences'));
    }

   public function mesCours()
    {
        $etudiant = auth()->user()->etudiant;

        $cours = Cours::with(['matiere', 'professeur.user', 'salle'])
            ->where('idClasse', $etudiant->idClasse)
            ->where('heureDebut', '>=', now())   // on exclut les cours déjà passés
            ->orderBy('heureDebut', 'asc')        // le plus proche en premier
            ->paginate(15);

        return view('etudiant.cours', compact('cours'));
    }
    public function mesEtudiants()
    {
        $professeur = auth()->user()->professeur;
        $classIds   = Cours::where('professeur_id', $professeur->id)->pluck('idClasse')->unique();

        $etudiants  = Etudiant::with(['user', 'inscriptionActuelle.classe'])
            ->whereIn('idClasse', $classIds)
            ->paginate(20);

        return view('professeur.etudiants', compact('etudiants'));
    }
}