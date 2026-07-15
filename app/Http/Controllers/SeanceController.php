<?php

namespace App\Http\Controllers;

use App\Models\Seance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SeanceController extends Controller {
    
    // Action qui affiche la page du Chef de Service (Formulaire + Salles)
    public function tempsReelSalles() {
        $maintenant = Carbon::now('Africa/Dakar');
        $heureActuelle = $maintenant->format('H:i:s');
        $dateAujourdhui = $maintenant->format('Y-m-d');

        // On prend tous les utilisateurs pour les tests comme configuré
        $professeurs = User::all(); 

        // Récupérer les séances officiellement "en cours" (via le statut du prof) 
        // OU qui correspondent au créneau horaire prévu aujourd'hui
        $seancesEnCours = Seance::with('professeur')
            ->where('date_seance', $dateAujourdhui)
            ->where(function($query) use ($heureActuelle) {
                $query->where('statut', 'en_cours')
                      ->orWhere(function($q) use ($heureActuelle) {
                          $q->where('heure_debut', '<=', $heureActuelle)
                            ->where('heure_fin', '>=', $heureActuelle)
                            ->where('statut', 'planifie');
                      });
            })
            ->get();

        // Cherche dans le dossier "chef"
        return view('chef.salles', compact('seancesEnCours', 'heureActuelle', 'professeurs'));
    }

    // Traitement du formulaire quand le Chef de Service clique sur "Valider"
    public function store(Request $request) {
        $request->validate([
            'nom_cours' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'salle' => 'required|string',
            'classe' => 'required|string',
            'date_seance' => 'required|date',
            'heure_debut' => 'required',
            'heure_fin' => 'required',
        ]);

        // Vérification anti-conflit d'origine
        $conflit = Seance::where('salle', $request->salle)
            ->where('date_seance', $request->date_seance)
            ->where(function($query) use ($request) {
                $query->whereBetween('heure_debut', [$request->heure_debut, $request->heure_fin])
                      ->orWhereBetween('heure_fin', [$request->heure_debut, $request->heure_fin]);
            })->exists();

        if ($conflit) {
            return back()->withErrors(['salle' => 'Attention : Cette salle est déjà occupée à ce créneau !']);
        }

        // Sauvegarde avec le statut initial par défaut 'planifie'
        $data = $request->all();
        $data['statut'] = 'planifie';

        Seance::create($data);

        return redirect()->back()->with('success', 'Le cours a été attribué avec succès par le Chef de Service !');
    }

    // Action pour l'emploi du temps de la semaine du Professeur
    public function monPlanning() {
        // Prendre la date d'aujourd'hui et calculer le début (lundi) et la fin de la semaine
        $debutSemaine = Carbon::now()->startOfWeek()->format('Y-m-d');
        $finSemaine = Carbon::now()->endOfWeek()->format('Y-m-d');

        // Récupérer uniquement les séances du professeur connecté pour cette semaine
        $mesSeances = Seance::where('user_id', auth()->id())
            ->whereBetween('date_seance', [$debutSemaine, $finSemaine])
            ->orderBy('date_seance', 'asc')
            ->orderBy('heure_debut', 'asc')
            ->get()
            ->groupBy('date_seance'); // Regrouper par jour pour l'affichage

        return view('professeur.planning', compact('mesSeances'));
    }

    /**
     * Action lancée par le prof pour démarrer le cours (ouvre le pointage pour 30 min)
     */
    public function demarrerCours($id) {
        $seance = Seance::findOrFail($id);

        // Protection : On ne peut démarrer qu'un cours planifié
        if ($seance->statut !== 'planifie') {
            return redirect()->back()->with('error', 'Impossible : Ce cours a déjà été démarré ou est terminé.');
        }

        // Passage du statut à "en_cours" et enregistrement du moment exact de démarrage
        $seance->update([
            'statut' => 'en_cours',
            'heure_demarrage_reel' => Carbon::now('Africa/Dakar'),
        ]);

        return redirect()->back()->with('success', 'Le cours a été démarré avec succès ! Le pointage faciale est actif pour une durée de 30 minutes.');
    }

    /**
     * Action lancée par le prof pour fermer le cours à la fin de la séance
     */
    public function cloturerCours($id) {
        $seance = Seance::findOrFail($id);

        // Protection : On ne peut clore qu'un cours qui est actuellement actif
        if ($seance->statut !== 'en_cours') {
            return redirect()->back()->with('error', 'Impossible : Vous ne pouvez pas clôturer un cours qui n\'est pas commencé.');
        }

        // Clôture du cours
        $seance->update([
            'statut' => 'termine',
            'heure_cloture_reelle' => Carbon::now('Africa/Dakar'),
        ]);

        return redirect()->back()->with('success', 'Le cours a été clôturé et archivé avec succès.');
    }
}