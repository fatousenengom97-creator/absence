<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClasseController extends Controller
{
    /**
     * Affiche la vue principale de gestion des classes.
     */
    public function index()
    {
        return view('admin.classes.index');
    }

    /**
     * SIMULATION API : Filtre et retourne les classes selon les critères stricts du Front-end
     */
    public function getClassesByCriteria(Request $request)
{
    // Récupération des paramètres
    $dept = $request->query('dept', 'TIC');
    $filiere = $request->query('filiere', 'D2A');
    $niveau = $request->query('niveau', 'L1');

    // Catalogue complet simulé
    $classesCatalogue = [
        // --- DÉPARTEMENT TIC ---
        ['id' => 1, 'nom' => 'L1 D2A', 'filiere' => 'D2A', 'dept' => 'TIC', 'niveau' => 'L1', 'annee' => '2025-2026', 'effectif' => 45],
        ['id' => 2, 'nom' => 'L2 D2A', 'filiere' => 'D2A', 'dept' => 'TIC', 'niveau' => 'L2', 'annee' => '2025-2026', 'effectif' => 38],
        ['id' => 3, 'nom' => 'L3 D2A', 'filiere' => 'D2A', 'dept' => 'TIC', 'niveau' => 'L3', 'annee' => '2025-2026', 'effectif' => 42],
        ['id' => 4, 'nom' => 'L1 SRT', 'filiere' => 'SRT', 'dept' => 'TIC', 'niveau' => 'L1', 'annee' => '2025-2026', 'effectif' => 50],
        ['id' => 5, 'nom' => 'L2 SRT', 'filiere' => 'SRT', 'dept' => 'TIC', 'niveau' => 'L2', 'annee' => '2025-2026', 'effectif' => 47],
        ['id' => 6, 'nom' => 'L3 SRT', 'filiere' => 'SRT', 'dept' => 'TIC', 'niveau' => 'L3', 'annee' => '2025-2026', 'effectif' => 40],

        // --- DÉPARTEMENT MPC ---
        ['id' => 7, 'nom' => 'L1 MPCI (SID)', 'filiere' => 'MPCI', 'dept' => 'MPC', 'niveau' => 'L1', 'annee' => '2025-2026', 'effectif' => 60],
        ['id' => 10, 'nom' => 'L1 MPCI (PC)', 'filiere' => 'MPCI', 'dept' => 'MPC', 'niveau' => 'L1', 'annee' => '2025-2026', 'effectif' => 55],
        ['id' => 8, 'nom' => 'L2 MPI (SID)', 'filiere' => 'MPI', 'dept' => 'MPC', 'niveau' => 'L2', 'annee' => '2025-2026', 'effectif' => 32],
        ['id' => 11, 'nom' => 'L2 PC', 'filiere' => 'PC', 'dept' => 'MPC', 'niveau' => 'L2', 'annee' => '2025-2026', 'effectif' => 28],
        ['id' => 9, 'nom' => 'L3 SID', 'filiere' => 'SID', 'dept' => 'MPC', 'niveau' => 'L3', 'annee' => '2025-2026', 'effectif' => 30],
        ['id' => 12, 'nom' => 'L3 PC', 'filiere' => 'PC', 'dept' => 'MPC', 'niveau' => 'L3', 'annee' => '2025-2026', 'effectif' => 26],
        ['id' => 15, 'nom' => 'L3 PN', 'filiere' => 'PN', 'dept' => 'MPC', 'niveau' => 'L3', 'annee' => '2025-2026', 'effectif' => 22],
    ];

    // 1. Essai de filtrage strict
    $classesFiltrees = array_filter($classesCatalogue, function ($classe) use ($dept, $filiere, $niveau) {
        return $classe['dept'] === $dept && 
               $classe['filiere'] === $filiere && 
               $classe['niveau'] === $niveau;
    });

    // 2. SÉCURITÉ anti-tableau vide : Si le filtre strict ne donne rien,
    // on renvoie toutes les classes du département sélectionné pour ne pas bloquer l'interface !
    if (empty($classesFiltrees)) {
        $classesFiltrees = array_filter($classesCatalogue, function ($classe) use ($dept) {
            return $classe['dept'] === $dept;
        });
    }

    return response()->json(array_values($classesFiltrees));
}
    /**
     * SIMULATION API : Récupère une classe pour édition
     */
    public function edit($id)
    {
        return response()->json([
            'id' => $id,
            'nom' => 'Classe Simulée #' . $id,
            'annee' => '2025-2026',
            'effectif' => rand(25, 45)
        ]);
    }

    public function store(Request $request) { return response()->json(['success' => true]); }
    public function update(Request $request, $id) { return response()->json(['success' => true]); }
    public function destroy($id) { return response()->json(['success' => true]); }
}