<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CoursController extends Controller
{
    /**
     * Affiche l'interface de planification des cours pour l'administrateur.
     */
    public function index()
    {
        // Renvoie vers la vue d'administration qu'on a créée
        return view('admin.cours.index');
    }

    // Tu pourras ajouter plus tard tes méthodes de stockage ou de suppression si tu relies la BDD :
    // public function store(Request $request) { ... }
    // public function destroy($id) { ... }
}