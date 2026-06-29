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
        $professeur = auth()->user()->professeur;
        $absences   = Absence::with(['etudiant.user', 'cours.matiere', 'cours.classe'])
            ->whereHas('cours', fn($q) => $q->where('professeur_id', $professeur->id))
            ->when($request->date, fn($q) => $q->whereDate('date', $request->date))
            ->latest('date')->paginate(20);

        return view('professeur.absences', compact('absences'));
    }

    public function declarerPointage(Request $request, Cours $cours)
    {
        $cours->update(['statut' => 'en_cours']);
        return redirect()->route('biometrie.pointage', $cours)->with('info', 'Session de pointage démarrée.');
    }
}