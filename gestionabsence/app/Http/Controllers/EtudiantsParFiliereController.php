<?php

namespace App\Http\Controllers;

use App\Models\{Filiere, Classe};
use Illuminate\Http\Request;

class EtudiantsParFiliereController extends Controller
{
    public function index()
    {
        $filieres = Filiere::with('departement')
            ->withCount('classes')
            ->orderBy('nomFiliere')
            ->get();

        return view('admin.etudiants-filiere.index', compact('filieres'));
    }

    public function showClasses(Filiere $filiere)
    {
        $filiere->load(['classes' => fn($q) => $q->withCount('etudiants')->with('niveau')]);
        $classesParNiveau = $filiere->classes
            ->sortBy(fn($c) => $c->niveau->nom ?? '')
            ->groupBy(fn($c) => $c->niveau->nom ?? 'Sans niveau');

        return view('admin.etudiants-filiere.classes', compact('filiere', 'classesParNiveau'));
    }

    public function showEtudiants(Filiere $filiere, Classe $classe)
    {
        $classe->load(['niveau', 'etudiants.user']);
        $etudiants = $classe->etudiants()->with('user')->paginate(15);

        return view('admin.etudiants-filiere.etudiants', compact('filiere', 'classe', 'etudiants'));
    }
}