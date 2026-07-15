<?php

namespace App\Http\Controllers;

use App\Models\{Cours, Matiere, Classe, Salle, Absence, Etudiant};
use Illuminate\Http\Request;
use Carbon\Carbon;

class CoursController extends Controller
{
    public function index()
    {
        $professeur = auth()->user()->professeur;

        if ($professeur) {
            // Vue professeur : ses propres cours
            $cours = Cours::with(['matiere', 'classe', 'salle'])
                ->where('professeur_id', $professeur->id)
                ->orderByDesc('heureDebut')
                ->paginate(15);
        } else {
            // Vue admin : tous les cours
            $cours = Cours::with(['matiere', 'classe', 'salle', 'professeur.user'])
                ->orderByDesc('heureDebut')
                ->paginate(15);
        }

        return view('cours.index', compact('cours'));
    }

    public function create()
    {
        $matieres    = Matiere::orderBy('nomMatiere')->get();
        $classes     = Classe::with(['filiere', 'niveau'])->orderBy('nom')->get();
        $salles      = Salle::orderBy('nom')->get();
        $professeurs = \App\Models\Professeur::with('user')->get();
        return view('cours.create', compact('matieres', 'classes', 'salles', 'professeurs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'professeur_id' => 'required|exists:professeurs,id',
            'idMatiere'     => 'required|exists:matieres,idMatiere',
            'idClasse'      => 'required|exists:classes,idClasse',
            'idSalle'       => 'required|exists:salles,idSalle',
            'typeCours'     => 'required|in:CM,TD,TP',
            'date'          => 'required|date',
            'heureDebut'    => 'required',
            'heureFin'      => 'required',
        ]);

        $heureDebut = $data['date'] . ' ' . $data['heureDebut'] . ':00';
        $heureFin   = $data['date'] . ' ' . $data['heureFin'] . ':00';

        // Vérifier que la salle est disponible
        $salle = \App\Models\Salle::find($data['idSalle']);
        if ($salle->estOccupee($heureDebut, $heureFin)) {
            return back()
                ->withInput()
                ->withErrors(['idSalle' => 'Cette salle est déjà occupée sur ce créneau horaire.']);
        }

        // Vérifier que le professeur est disponible
        $conflitProf = Cours::where('professeur_id', $data['professeur_id'])
            ->where('statut', '!=', 'annule')
            ->where('heureDebut', '<', $heureFin)
            ->where('heureFin', '>', $heureDebut)
            ->exists();

        if ($conflitProf) {
            return back()
                ->withInput()
                ->withErrors(['professeur_id' => 'Ce professeur a déjà un cours sur ce créneau horaire.']);
        }

        Cours::create([
            'idMatiere'     => $data['idMatiere'],
            'professeur_id' => $data['professeur_id'],
            'idClasse'      => $data['idClasse'],
            'idSalle'       => $data['idSalle'],
            'typeCours'     => $data['typeCours'],
            'heureDebut'    => $heureDebut,
            'heureFin'      => $heureFin,
            'jour'          => \Carbon\Carbon::parse($data['date'])->locale('fr')->dayName,
            'statut'        => 'planifie',
        ]);

        return redirect()->route('cours.index')->with('success', 'Cours créé et attribué avec succès.');
    }   

    public function show(Cours $cours)
    {
        $cours->load(['matiere', 'classe', 'salle', 'absences.etudiant.user']);
        return view('cours.show', compact('cours'));
    }

    public function edit(Cours $cours)
    {
        $matieres = Matiere::orderBy('nomMatiere')->get();
        $classes  = Classe::orderBy('nom')->get();
        $salles   = Salle::orderBy('nom')->get();
        return view('cours.edit', compact('cours', 'matieres', 'classes', 'salles'));
    }

    public function update(Request $request, Cours $cours)
    {
        $data = $request->validate([
            'idMatiere'  => 'required|exists:matieres,idMatiere',
            'idClasse'   => 'required|exists:classes,idClasse',
            'idSalle'    => 'required|exists:salles,idSalle', 
            'typeCours'  => 'required|in:CM,TD,TP',
            'date'       => 'required|date',
            'heureDebut' => 'required',
            'heureFin'   => 'required',
        ]);

        $heureDebut = $data['date'] . ' ' . $data['heureDebut'] . ':00';
        $heureFin   = $data['date'] . ' ' . $data['heureFin'] . ':00';

        $cours->update([
            'idMatiere'  => $data['idMatiere'],
            'idClasse'   => $data['idClasse'],
            'idSalle'    => $data['idSalle'],
            'typeCours'  => $data['typeCours'],
            'heureDebut' => $heureDebut,
            'heureFin'   => $heureFin,
            'jour'       => Carbon::parse($data['date'])->locale('fr')->dayName,
        ]);

        return redirect()->route('cours.index')->with('success', 'Cours mis à jour.');
    }

    public function destroy(Cours $cours)
    {
        $cours->delete();
        return redirect()->route('cours.index')->with('success', 'Cours supprimé.');
    }

    /* ---- Démarrer un cours ---- */
    public function demarrer(Cours $cours)
    {
        $cours->update(['statut' => 'en_cours']);

        // Créer automatiquement les absences pour tous les étudiants de la classe
        $etudiants = Etudiant::whereHas('inscriptions', fn($q) =>
            $q->where('idClasse', $cours->idClasse)
        )->get();

        foreach ($etudiants as $etudiant) {
            Absence::updateOrCreate(
                [
                    'etudiant_id' => $etudiant->id,
                    'idCours'     => $cours->idCours,
                    'date'        => today(),
                ],
                ['statut' => 'absent'] // par défaut absent, le pointage facial les marquera présents
            );
        }

        // CORRECTION : Redirection vers la route biométrique avec le bon nom et l'id du cours en paramètre
        return redirect()->route('biometrie.pointage', ['cours' => $cours->idCours])
            ->with('success', 'Cours démarré — pointage facial lancé.');
    }

    /* ---- Terminer un cours ---- */
    public function terminer(Cours $cours)
    {
        $cours->update(['statut' => 'termine']);
        return redirect()->route('cours.index')->with('success', 'Cours terminé.');
    }
}