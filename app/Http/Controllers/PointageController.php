<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absence;
use App\Models\Etudiant;
use App\Models\Cours;

class PointageController extends Controller
{
    public function validerPresence(Request $request)
    {
        // 1. Validation des données reçues
        $request->validate([
            'etudiant_id' => 'required|exists:etudiants,id',
            'cours_id'    => 'required|exists:cours,id',
        ]);

        // 2. Récupérer le cours et vérifier si le pointage est bien ouvert
        $cours = Cours::find($request->cours_id);
        
        if (!$cours || $cours->statut_pointage !== 'ouvert') {
            return response()->json([
                'success' => false, 
                'message' => 'Le pointage pour ce cours n\'est pas actif ou est fermé.'
            ], 403);
        }

        // 3. Trouver la fiche de présence/absence pour ce cours et cet étudiant
        // On charge la relation 'etudiant' pour pouvoir lire le prénom sans souci
        $absence = Absence::with('etudiant')
            ->where('cours_id', $request->cours_id)
            ->where('etudiant_id', $request->etudiant_id)
            ->first();

        if ($absence) {
            // Si l'étudiant était marqué absent (ou pas encore pointé), on valide sa présence
            $absence->update([
                'statut' => 'present', // s'accorde avec ton système (ex: 'present')
            ]);

            // Récupérer le prénom de l'étudiant proprement
            $prenomEtudiant = $absence->etudiant->prenom ?? 'l\'étudiant';
            $nomEtudiant = $absence->etudiant->nom ?? '';

            return response()->json([
                'success' => true, 
                'message' => "Présence validée pour {$prenomEtudiant} {$nomEtudiant} !"
            ]);
        }

        // 4. Si aucune ligne d'absence/présence n'existe, on peut la créer à la volée comme présente !
        $etudiant = Etudiant::find($request->etudiant_id);
        if ($etudiant) {
            Absence::create([
                'cours_id'    => $request->cours_id,
                'etudiant_id' => $request->etudiant_id,
                'statut'      => 'present',
                'date'        => now(),
            ]);

            return response()->json([
                'success' => true, 
                'message' => "Présence enregistrée pour {$etudiant->prenom} {$etudiant->nom} !"
            ]);
        }

        return response()->json([
            'success' => false, 
            'message' => 'Étudiant introuvable.'
        ], 404);
    }
}