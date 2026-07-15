<?php

namespace App\Http\Controllers;

use App\Models\{Departement, Filiere, Classe, UE, Matiere};
use Illuminate\Http\Request;

class DepartementController extends Controller
{
    public function index()
    {
        $departements = Departement::withCount('filieres')->get();
        return view('admin.departements.index', compact('departements'));
    }

    public function store(Request $request)
    {
        $request->validate(['nomDep' => 'required|string|max:150|unique:departements,nomDep']);
        Departement::create(['nomDep' => $request->nomDep]);
        return redirect()->route('admin.departements.index')->with('success', 'Département créé.');
    }

    public function showFilieres(Departement $departement)
    {
        $departement->load(['filieres' => fn($q) => $q->withCount('classes')]);
        return view('admin.departements.filieres', compact('departement'));
    }

    public function storeFiliere(Request $request, Departement $departement)
    {
        $request->validate(['nomFiliere' => 'required|string|max:150']);
        Filiere::create([
            'nomFiliere' => $request->nomFiliere,
            'idDep'      => $departement->idDep,
        ]);
        return redirect()->route('admin.departements.filieres', $departement)->with('success', 'Filière créée.');
    }

    public function showClasses(Departement $departement, Filiere $filiere)
    {
        $filiere->load(['classes' => fn($q) => $q->withCount('etudiants')->with('niveau')]);
        $classesParNiveau = $filiere->classes->sortBy(fn($c) => $c->niveau->nom ?? '')->groupBy(fn($c) => $c->niveau->nom ?? 'Sans niveau');

        return view('admin.departements.classes', compact('departement', 'filiere', 'classesParNiveau'));
    }

    public function showMatieres(Departement $departement, Filiere $filiere, Classe $classe)
    {
        $classe->load(['ues.matieres', 'niveau', 'etudiants']);
        return view('admin.departements.matieres', compact('departement', 'filiere', 'classe'));
    }

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
            ->with('success', 'Matière (EC) ajoutée.');
    }
}