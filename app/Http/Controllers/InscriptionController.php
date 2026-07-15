<?php

namespace App\Http\Controllers;

use App\Models\{Departement, Filiere, Classe};
use Illuminate\Http\Request;

class InscriptionController extends Controller
{
    /* ---- Retourne les filières d'un département (appelé en AJAX) ---- */
    public function filieresParDepartement(Departement $departement)
    {
        $filieres = $departement->filieres()->orderBy('nomFiliere')->get(['idFiliere', 'nomFiliere']);
        return response()->json($filieres);
    }

    /* ---- Retourne les classes d'une filière, groupées par niveau (appelé en AJAX) ---- */
    public function classesParFiliere(Filiere $filiere)
    {
        $classes = $filiere->classes()
            ->with('niveau')
            ->get(['idClasse', 'nom', 'idNiveau'])
            ->sortBy(fn($c) => $c->niveau->nom ?? '');

        return response()->json($classes->values());
    }
}