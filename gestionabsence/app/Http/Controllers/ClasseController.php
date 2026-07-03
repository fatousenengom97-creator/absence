<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use Illuminate\Http\Request;

class ClasseController extends Controller
{
    /**
     * Affiche l'arborescence des départements, filières et classes pour Fatou.
     */
    public function index()
    {
        // Récupère les départements, leurs filières associées, les classes de ces filières, et leur niveau
        $departements = Departement::with(['filieres.classes.niveau'])->get();

        // Envoie les données à ta vue d'administration
        return view('admin.classes.index', compact('departements'));
    }
}