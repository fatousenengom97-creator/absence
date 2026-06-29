<?php

namespace App\Http\Controllers;

use App\Models\{DonneesBiometriques, Etudiant, Absence, Cours};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BiometrieController extends Controller
{
    /* ---- Page d'enregistrement biométrique ---- */
    public function enregistrer(Etudiant $etudiant)
    {
        $biometrie = $etudiant->donneesBiometriques()->latest()->first();
        return view('biometrie.enregistrer', compact('etudiant', 'biometrie'));
    }

    /* ---- Sauvegarder le vecteur facial ---- */
    public function sauvegarder(Request $request, Etudiant $etudiant)
    {
        $request->validate([
            'face_vector' => 'required|string',
            'photo'       => 'required|string', // base64
        ]);

        // Décoder et sauvegarder la photo
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->photo));
        $filename  = 'biometrie/etudiant_' . $etudiant->id . '_' . time() . '.jpg';
        Storage::disk('public')->put($filename, $imageData);

        DonneesBiometriques::updateOrCreate(
            ['etudiant_id' => $etudiant->id],
            [
                'faceVector'      => $request->face_vector,
                'cheminPhoto'     => $filename,
                'dateEnregistre'  => now(),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Données biométriques enregistrées.']);
    }

    /* ---- Page de pointage facial ---- */
    public function pointage(Cours $cours)
    {
        $cours->load(['classe.etudiants.donneesBiometriques', 'matiere']);
        return view('biometrie.pointage', compact('cours'));
    }

    /* ---- Traitement du pointage ---- */
    public function traiterPointage(Request $request, Cours $cours)
    {
        $request->validate([
            'etudiant_id' => 'required|exists:etudiants,id',
            'confiance'   => 'required|numeric|min:0|max:1',
        ]);

        $seuilConfiance = 0.75;

        if ($request->confiance >= $seuilConfiance) {
            Absence::updateOrCreate(
                [
                    'etudiant_id' => $request->etudiant_id,
                    'idCours'     => $cours->idCours,
                    'date'        => today(),
                ],
                [
                    'statut'          => 'present',
                    'pointage_facial' => true,
                ]
            );

            // Enregistrer l'heure d'entrée
            DonneesBiometriques::where('etudiant_id', $request->etudiant_id)
                ->update(['heureEntre' => now()]);

            $etudiant = Etudiant::with('user')->find($request->etudiant_id);
            return response()->json([
                'success'  => true,
                'message'  => 'Présence confirmée pour ' . $etudiant->full_name,
                'confiance'=> round($request->confiance * 100) . '%',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Visage non reconnu. Confiance insuffisante (' . round($request->confiance * 100) . '%).',
        ], 422);
    }

    /* ---- Liste des étudiants avec biométrie ---- */
    public function index()
    {
        $etudiants = Etudiant::with(['user', 'donneesBiometriques', 'classe'])->paginate(20);
        return view('biometrie.index', compact('etudiants'));
    }
}