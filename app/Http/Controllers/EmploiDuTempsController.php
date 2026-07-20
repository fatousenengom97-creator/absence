<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Cours;
use App\Models\Matiere;
use App\Models\Salle;
use App\Models\User; // En supposant que tes professeurs sont des Users avec un rôle
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmploiDuTempsController extends Controller
{
    /**
     * Afficher l'emploi du temps d'une classe pour une semaine donnée.
     */
    public function show(Request $request, $idClasse)
    {
        $classe = Classe::findOrFail($idClasse);

        // 1. Déterminer le début de la semaine (Lundi) basé sur la requête ou la date du jour (19/07/2026)
        // Si le paramètre 'semaine' est fourni (ex: 2026-07-20), on se base dessus, sinon date actuelle
        $dateRef = $request->get('semaine') ? Carbon::parse($request->get('semaine')) : Carbon::now();
        $debutSemaine = $dateRef->startOfWeek(); // Lundi
        $finSemaine = $debutSemaine->copy()->endOfWeek(); // Dimanche

        // 2. Récupérer tous les cours de la classe pour cette plage de dates
        $cours = Cours::with(['matiere', 'salle', 'professeur'])
            ->where('idClasse', $idClasse)
            ->whereBetween('date', [$debutSemaine->toDateString(), $finSemaine->toDateString()])
            ->get();

        // 3. Charger les données nécessaires pour le formulaire d'ajout
        $matieres = Matiere::all();
        $salles = Salle::all();
        $professeurs = User::where('role', 'professeur')->get(); // Adapte selon ton modèle de rôles

        return view('biometrie.emploi_du_temps', compact(
            'classe', 
            'cours', 
            'debutSemaine', 
            'matieres', 
            'salles', 
            'professeurs'
        ));
    }

    /**
     * Enregistrer un nouveau créneau horaire avec détection des conflits.
     */
    public function store(Request $request)
    {
        // 1. Validation basique des données
        $request->validate([
            'date'        => 'required|date',
            'heure_debut' => 'required',
            'heure_fin'   => 'required|after:heure_debut',
            'idClasse'    => 'required|exists:classes,id',
            'idMatiere'   => 'required|exists:matieres,id',
            'idSalle'     => 'required|exists:salles,id',
            'professeur_id'=> 'required|exists:users,id',
            'type'        => 'required|in:CM,TD,TP',
            'couleur'     => 'nullable|string'
        ]);

        $date = $request->date;
        $debut = $request->heure_debut;
        $fin = $request->heure_fin;
        $idClasse = $request->idClasse;

        // 2. Vérification de conflit : Une classe ne peut pas avoir deux cours à la même heure
        $conflitClasse = Cours::where('idClasse', $idClasse)
            ->where('date', $date)
            ->where(function ($query) use ($debut, $fin) {
                $query->whereBetween('heureDebut', [$debut, $fin])
                      ->orWhereBetween('heureFin', [$debut, $fin])
                      ->orWhere(function ($q) use ($debut, $fin) {
                          $q->where('heureDebut', '<=', $debut)
                            ->where('heureFin', '>=', $fin);
                      });
            })->exists();

        if ($conflitClasse) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['erreur_conflit' => '⚠️ Conflit d\'horaire : Cette classe a déjà un cours programmé sur ce créneau !']);
        }

        // 3. Si aucun conflit, enregistrement en base de données
        Cours::create([
            'date'         => $date,
            'heureDebut'   => $debut,
            'heureFin'     => $fin,
            'idClasse'     => $idClasse,
            'idMatiere'    => $request->idMatiere,
            'idSalle'      => $request->idSalle,
            'professeur_id'=> $request->professeur_id,
            'type'         => $request->type,
            'couleur'      => $request->couleur ?? '#3b82f6',
        ]);

        return redirect()->back()->with('success', 'Créneau horaire ajouté avec succès !');
    }
}