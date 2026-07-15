<?php

namespace App\Http\Controllers;

use App\Models\{Absence, Cours, Etudiant, Classe, Filiere};
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AbsenceController extends Controller
{
    /* ---- Liste des absences classées et filtrées ---- */
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Absence::with(['etudiant.user', 'cours.matiere', 'cours.classe.filiere', 'cours.classe.niveau']);

        // 1. SÉCURITÉ : Restriction des accès selon le rôle
        if ($user->isEtudiant()) {
            $query->where('etudiant_id', $user->etudiant->id);
        } elseif ($user->isProfesseur()) {
            $query->whereHas('cours', fn($q) => $q->where('professeur_id', $user->professeur->id));
        }

        // 2. FILTRES DYNAMIQUES (Filière -> Niveau -> Classe)
        if ($request->filled('filiere')) {
            $query->whereHas('cours.classe', fn($q) => $q->where('idFiliere', $request->filiere));
        }
        
        if ($request->filled('niveau')) {
            $query->whereHas('cours.classe', fn($q) => $q->where('idNiveau', $request->niveau));
        }

        if ($request->filled('classe')) {
            $query->whereHas('cours', fn($q) => $q->where('idClasse', $request->classe));
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Récupération des absences paginées
        $absences = $query->latest('date')->paginate(20);

        // 3. RÉORGANISATION : Regroupement de la liste pour l'affichage (si pas étudiant)
        $absencesGroupees = null;
        if (!$user->isEtudiant()) {
            $absencesGroupees = $absences->groupBy([
                fn($item) => $item->cours->classe->filiere->nomFiliere ?? 'Sans Filière',
                fn($item) => $item->cours->classe->niveau->nom ?? 'Sans Niveau',
                fn($item) => $item->cours->classe->nomClasse ?? 'Sans Classe'
            ]);
        }

        // Données pour remplir les listes déroulantes des filtres dans la vue
        $filieres = Filiere::with('classes.niveau')->orderBy('nomFiliere')->get();

        return view('absences.index', compact('absences', 'absencesGroupees', 'filieres'));
    }

    /* ---- Feuille de présence par cours ---- */
    public function feuille(Cours $cours)
    {
        $user = auth()->user();

        // SÉCURITÉ : Un professeur ne peut voir que la feuille de son propre cours
        if ($user->isProfesseur() && $cours->professeur_id !== $user->professeur->id) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à la feuille de présence de ce cours.');
        }

        $cours->load(['matiere', 'classe.etudiants.user', 'absences.etudiant.user']);

        // Initialiser les absences pour les étudiants qui n'en ont pas encore
        foreach ($cours->classe->etudiants as $etudiant) {
            Absence::firstOrCreate(
                ['etudiant_id' => $etudiant->id, 'idCours' => $cours->idCours, 'date' => today()],
                ['statut' => 'absent']
            );
        }

        $cours->refresh()->load('absences.etudiant.user');
        return view('absences.feuille', compact('cours'));
    }

    /* ---- Enregistrer la présence manuellement ---- */
    public function enregistrer(Request $request, Cours $cours)
    {
        $user = auth()->user();

        // SÉCURITÉ : Seul le professeur du cours peut enregistrer les présences
        if ($user->isProfesseur() && $cours->professeur_id !== $user->professeur->id) {
            abort(403, 'Action non autorisée.');
        }

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

    /* ---- Valider / Modifier une absence (Professeur) ---- */
    public function valider(Request $request, Absence $absence)
    {
        $user = auth()->user();

        // SÉCURITÉ : Le professeur ne peut modifier que les absences liées à ses propres cours
        if ($user->isProfesseur() && $absence->cours->professeur_id !== $user->professeur->id) {
            abort(403, 'Action non autorisée.');
        }

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
        $user  = auth()->user();
        $query = Absence::with(['etudiant.user', 'cours.matiere', 'cours.classe']);

        // SÉCURITÉ : Filtrage du contenu du PDF selon l'utilisateur connecté
        if ($user->isEtudiant()) {
            $query->where('etudiant_id', $user->etudiant->id);
        } elseif ($user->isProfesseur()) {
            $query->whereHas('cours', fn($q) => $q->where('professeur_id', $user->professeur->id));
        }

        // Filtres optionnels
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