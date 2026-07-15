<?php

namespace App\Http\Controllers;

use App\Models\EmploiDuTemps;
use App\Models\Classe;
use App\Models\Cours;
use App\Models\Absence;
use App\Models\Etudiant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EtudiantController extends Controller
{
    public function dashboard()
    {
        // 1. Récupérer l'étudiant connecté et sa classe
        $etudiant = auth()->user()->etudiant;
        
        // On utilise idClasse qui est ta clé primaire/étrangère
        $classe = $etudiant ? Classe::find($etudiant->idClasse) : null; 

        $timezone = 'Africa/Dakar';

        // 2. Récupérer les cours d'aujourd'hui pour sa classe
        $coursAujourdhui = [];
        if ($classe) {
            $coursAujourdhui = Cours::with(['matiere', 'salle', 'professeur.user'])
                ->where('idClasse', $classe->idClasse)
                ->whereDate('heureDebut', Carbon::today($timezone))
                ->orderBy('heureDebut')
                ->get();
        }

        // 3. Prochains cours de la semaine (à partir de maintenant)
        $prochainsCours = [];
        if ($classe) {
            $prochainsCours = Cours::with(['matiere', 'salle', 'professeur.user'])
                ->where('idClasse', $classe->idClasse)
                ->where('heureDebut', '>', Carbon::now($timezone))
                ->whereBetween('heureDebut', [
                    Carbon::now($timezone)->startOfWeek(),
                    Carbon::now($timezone)->endOfWeek()
                ])
                ->orderBy('heureDebut')
                ->get();
        }

        // 4. Calculer ses statistiques d'absences
        $totalAbsences = Absence::where('etudiant_id', $etudiant?->id)
            ->where('statut', 'absent')
            ->count();

        $totalJustifies = Absence::where('etudiant_id', $etudiant?->id)
            ->where('statut', 'justifie')
            ->count();

        // 5. Retourner la vue de l'étudiant
        return view('etudiant.dashboard', compact(
            'etudiant', 
            'classe', 
            'coursAujourdhui',
            'prochainsCours', 
            'totalAbsences', 
            'totalJustifies'
        ));
    }

    public function mesCours()
    {
        $etudiant = auth()->user()->etudiant;
        $classe   = $etudiant ? Classe::find($etudiant->idClasse) : null;

        if ($classe) {
            $cours = Cours::with(['matiere', 'salle', 'professeur.user'])
                ->where('idClasse', $classe->idClasse)
                ->orderByDesc('heureDebut')
                ->paginate(15);
        } else {
            $cours = Cours::whereNull('idClasse')->paginate(15); // retourne un paginator vide
        }

        return view('etudiant.cours', compact('cours', 'classe'));
    }

    public function mesAbsences()
    {
        $etudiant  = auth()->user()->etudiant;
        $absences  = Absence::with(['cours.matiere', 'cours.classe'])
            ->where('etudiant_id', $etudiant?->id)
            ->latest('date')
            ->paginate(15);

        return view('etudiant.absences', compact('absences'));
    }

    public function monEmploiDuTemps()
    {
        // 1. On récupère l'étudiant connecté et sa classe
        $user = Auth::user();
        $etudiant = $user->etudiant; 
        
        if (!$etudiant || !$etudiant->idClasse) {
            return redirect()->back()->with('error', 'Vous n\'êtes inscrit dans aucune classe.');
        }

        $classe = Classe::with(['filiere', 'niveau'])->findOrFail($etudiant->idClasse);

        // 2. On récupère l'emploi du temps complet de cette classe, groupé par jour
        $edt = EmploiDuTemps::with(['matiere', 'professeur.user', 'salle'])
            ->where('idClasse', $classe->idClasse)
            ->where('actif', true)
            ->get()
            ->groupBy('jour');

        // 3. Variables de structure pour générer la grille (Lundi au Samedi inclus !)
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $heures = range(8, 18); // de 08h00 à 18h00

        // Redirection vers le dossier de tes vues étudiantes
        return view('etudiant.mon-edt', compact('classe', 'edt', 'jours', 'heures'));
    }
}