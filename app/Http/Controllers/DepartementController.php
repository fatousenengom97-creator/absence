<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use App\Models\Filiere;
use App\Models\Classe;
use App\Models\UE;
use App\Models\Matiere;
use Illuminate\Http\Request;

class DepartementController extends Controller
{
    // Affiche la liste des départements
    public function index()
    {
        $departements = Departement::withCount('filieres')->get();
        return view('admin.departements.index', compact('departements'));
    }

    // Enregistre un nouveau département
    public function store(Request $request)
    {
        $request->validate([
            'nomDep' => 'required|string|max:150|unique:departements,nomDep'
        ]);

        Departement::create([
            'nomDep' => $request->nomDep
        ]);

        return redirect()->route('admin.departements.index')->with('success', 'Département créé avec succès.');
    }

    // Affiche les filières d'un département
    public function showFilieres(Departement $departement)
    {
        $departement->load(['filieres' => fn($q) => $q->withCount('classes')]);
        return view('admin.departements.filieres', compact('departement'));
    }

    // Enregistre une filière dans un département
    public function storeFiliere(Request $request, Departement $departement)
    {
        $request->validate([
            'nomFiliere' => 'required|string|max:150'
        ]);

        Filiere::create([
            'nomFiliere' => $request->nomFiliere,
            'idDep'      => $departement->idDep,
        ]);

        return redirect()->route('admin.departements.filieres', $departement)->with('success', 'Filière créée avec succès.');
    }

    // Affiche les classes d'une filière
    public function showClasses(Departement $departement, Filiere $filiere)
    {
        $filiere->load(['classes' => fn($q) => $q->withCount('etudiants')->with('niveau')]);
        
        $classesParNiveau = $filiere->classes
            ->sortBy(fn($c) => $c->niveau->nom ?? '')
            ->groupBy(fn($c) => $c->niveau->nom ?? 'Sans niveau');

        return view('admin.departements.classes', compact('departement', 'filiere', 'classesParNiveau'));
    }

    // Affiche les matières et UEs d'une classe
    public function showMatieres(Departement $departement, Filiere $filiere, Classe $classe)
    {
        $classe->load(['ues.matieres', 'niveau', 'etudiants']);
        return view('admin.departements.matieres', compact('departement', 'filiere', 'classe'));
    }

    // Enregistre une Unité d'Enseignement (UE)
    public function storeUE(Request $request, Departement $departement, Filiere $filiere, Classe $classe)
    {
        $request->validate([
            'codeUE' => 'required|string|max:30',
            'nomUE'  => 'required|string|max:150',
        ]);

        UE::create([
            'codeUE'   => $request->codeUE,
            'nomUE'    => $request->nomUE,
            'idClasse' => $classe->idClasse,
        ]);

        return redirect()
            ->route('admin.departements.matieres', [$departement, $filiere, $classe])
            ->with('success', 'UE créée avec succès.');
    }

    // Enregistre un Élément Constitutif / Matière (EC) dans une UE
    public function storeMatiereUE(Request $request, Departement $departement, Filiere $filiere, Classe $classe, UE $ue)
    {
        $request->validate([
            'nomMatiere'  => 'required|string|max:150',
            'coefficient' => 'required|numeric|min:0',
        ]);

        Matiere::create([
            'nomMatiere'  => $request->nomMatiere,
            'codeUE'      => $ue->codeUE,
            'coefficient' => $request->coefficient,
            'idUE'        => $ue->idUE,
        ]);

        return redirect()
            ->route('admin.departements.matieres', [$departement, $filiere, $classe])
            ->with('success', 'Matière (EC) ajoutée avec succès.');
    }
}