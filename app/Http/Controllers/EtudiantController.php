<?php

namespace App\Http\Controllers;

use App\Models\{Cours, Absence, Etudiant, EmploiDuTemps};
use Illuminate\Http\Request;

class EtudiantController extends Controller
{
    public function dashboard()
    {
        $user     = auth()->user();
        $etudiant = Etudiant::where('user_id', $user->id)
            ->with(['inscriptionActuelle.classe.filiere'])
            ->first();

        // Récupérer la classe via idClasse direct (plus fiable)
        $classe = null;
        if ($etudiant) {
            if ($etudiant->inscriptionActuelle?->classe) {
                $classe = $etudiant->inscriptionActuelle->classe;
            } elseif ($etudiant->idClasse) {
                $classe = \App\Models\Classe::find($etudiant->idClasse);
            }
        }

        $coursAujourdhui   = collect();
        $edtClasse         = collect();
        $dernieresAbsences = collect();

        if ($classe) {
            // Cours du jour
            $coursAujourdhui = Cours::with(['matiere', 'salle', 'professeur.user'])
                ->where('idClasse', $classe->idClasse)
                ->whereDate('heureDebut', today())
                ->orderBy('heureDebut')
                ->get();

            // EDT fixe de la classe (table emplois_du_temps)
            $edtClasse = EmploiDuTemps::with(['matiere', 'salle', 'professeur.user'])
                ->where('idClasse', $classe->idClasse)
                ->where('actif', true)
                ->get();
        }

        $totalAbsences = Absence::where('etudiant_id', $etudiant?->id)
            ->where('statut', 'absent')->count();

        $totalJustifies = Absence::where('etudiant_id', $etudiant?->id)
            ->where('statut', 'justifie')->count();

        $dernieresAbsences = Absence::with(['cours.matiere'])
            ->where('etudiant_id', $etudiant?->id)
            ->latest('date')
            ->take(5)
            ->get();

        return view('etudiant.dashboard', compact(
            'etudiant', 'classe', 'coursAujourdhui', 'edtClasse',
            'totalAbsences', 'totalJustifies', 'dernieresAbsences'
        ));
    }

    public function mesAbsences()
    {
        $user     = auth()->user();
        $etudiant = Etudiant::where('user_id', $user->id)->first();

        $absences = Absence::with(['cours.matiere', 'cours.classe'])
            ->where('etudiant_id', $etudiant?->id)
            ->latest('date')
            ->paginate(15);

        return view('etudiant.absences', compact('absences'));
    }
}