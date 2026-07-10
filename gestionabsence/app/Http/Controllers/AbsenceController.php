<?php

namespace App\Http\Controllers;

use App\Models\{Absence, Cours, Etudiant, Classe};
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AbsenceController extends Controller
{
    /* ---- Liste des absences ---- */
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Absence::with(['etudiant.user', 'cours.matiere', 'cours.classe']);

        // Filtrage dynamique basé sur le rôle de l'utilisateur connecté
        if ($user->isEtudiant() && $user->etudiant) {
            $query->where('etudiant_id', $user->etudiant->id);
        } elseif ($user->isProfesseur() && $user->professeur) {
            $query->whereHas('cours', fn($q) => $q->where('professeur_id', $user->professeur->id));
        }

        // Filtres de recherche optionnels
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('classe')) {
            $query->whereHas('cours', fn($q) => $q->where('idClasse', $request->classe));
        }

        $absences = $query->latest('date')->paginate(20);
        $classes  = Classe::all();

        return view('absences.index', compact('absences', 'classes'));
    }

    /* ---- Feuille de présence par cours ---- */
    public function feuille(Cours $cours)
    {
        $cours->load(['matiere', 'classe.etudiants.user', 'absences.etudiant.user']);

        // Initialiser les absences pour les étudiants qui n'en ont pas encore
        if ($cours->classe && $cours->classe->etudiants) {
            foreach ($cours->classe->etudiants as $etudiant) {
                Absence::firstOrCreate(
                    ['etudiant_id' => $etudiant->id, 'idCours' => $cours->idCours, 'date' => today()],
                    ['statut' => 'absent']
                );
            }
        }

        $cours->refresh()->load('absences.etudiant.user');
        return view('absences.feuille', compact('cours'));
    }

    /* ---- Enregistrer la présence manuellement ---- */
    public function enregistrer(Request $request, Cours $cours)
    {
        $request->validate([
            'presences'   => 'required|array',
            'presences.*' => 'in:present,absent,retard,justifie',
        ]);

        foreach ($request->presences as $etudiantId => $statut) {
            Absence::updateOrCreate(
                ['etudiant_id' => $etudiantId, 'idCours' => $cours->idCours, 'date' => today()],
                ['statut' => $statut]
            );
        }

        $cours->update(['statut' => 'termine']);

        return redirect()->route('cours.show', $cours)->with('success', 'Présences enregistrées.');
    }

    /* ---- Valider une absence (Professeur) ---- */
    public function valider(Request $request, Absence $absence)
    {
        $request->validate(['statut' => 'required|in:present,absent,retard,justifie']);
        $absence->update(['statut' => $request->statut, 'justification' => $request->justification]);
        return back()->with('success', 'Statut mis à jour.');
    }

    /* ---- Justifier une absence (Étudiant) ---- */
    public function justifier(Request $request, Absence $absence)
    {
        $request->validate(['justification' => 'required|string|max:500']);

        if ($absence->etudiant->user_id !== auth()->id()) {
            abort(403);
        }

        $absence->update(['justification' => $request->justification, 'statut' => 'justifie']);
        return back()->with('success', 'Justification envoyée.');
    }

    /* ---- Rapport PDF ---- */
    public function rapport(Request $request)
    {
        $query = Absence::with(['etudiant.user', 'cours.matiere', 'cours.classe']);

        if ($request->filled('classe')) {
            $query->whereHas('cours', fn($q) => $q->where('idClasse', $request->classe));
        }
        if ($request->filled('mois')) {
            $query->whereMonth('date', $request->mois)->whereYear('date', $request->annee ?? now()->year);
        }

        $absences = $query->get();
        $pdf = Pdf::loadView('absences.rapport_pdf', compact('absences'))->setPaper('a4', 'landscape');
        return $pdf->stream('rapport_absences.pdf');
    }

    /* ---- Statistiques pour chef de service ---- */
    public function statistiques()
    {
        $classes = Classe::with(['etudiants'])->get()->map(function ($classe) {
            $total    = Absence::whereHas('cours', fn($q) => $q->where('idClasse', $classe->idClasse))->count();
            $absents  = Absence::whereHas('cours', fn($q) => $q->where('idClasse', $classe->idClasse))
                ->where('statut', 'absent')->count();
            $classe->taux_absence = $total > 0 ? round(($absents / $total) * 100, 1) : 0;
            return $classe;
        });

        return view('absences.statistiques', compact('classes'));
    }
}