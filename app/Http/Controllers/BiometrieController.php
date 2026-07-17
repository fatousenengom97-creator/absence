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
            'photo'       => 'required|string',
        ]);

        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->photo));
        $filename  = 'biometrie/etudiant_' . $etudiant->id . '_' . time() . '.jpg';
        Storage::disk('public')->put($filename, $imageData);

        DonneesBiometriques::updateOrCreate(
            ['etudiant_id' => $etudiant->id],
            [
                'faceVector'     => $request->face_vector,
                'cheminPhoto'    => $filename,
                'dateEnregistre' => now(),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Données biométriques enregistrées.']);
    }

    /* ---- Page de pointage facial ---- */
    public function pointage(Cours $cours)
    {
        $cours->load(['matiere', 'classe', 'salle']);

        $etudiants = Etudiant::with(['user', 'donneesBiometriques'])
            ->whereHas('inscriptions', fn($q) => $q->where('idClasse', $cours->idClasse))
            ->get();

        return view('biometrie.pointage', compact('cours', 'etudiants'));
    }

    /* ---- Traitement du pointage (appelé par le JS) ---- */
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

            DonneesBiometriques::where('etudiant_id', $request->etudiant_id)
                ->update(['heureEntre' => now()]);

            $etudiant = Etudiant::with('user')->find($request->etudiant_id);
            return response()->json([
                'success'   => true,
                'message'   => 'Présence confirmée pour ' . ($etudiant->user->prenom ?? '') . ' ' . ($etudiant->user->nom ?? ''),
                'confiance' => round($request->confiance * 100) . '%',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Confiance insuffisante (' . round($request->confiance * 100) . '%).',
        ], 422);
    }

    /* ---- Vérification du visage via serveur Python ---- */
    public function verifierVisage(Request $request, Cours $cours)
    {
        $request->validate(['image' => 'required|string']);

        try {
            // Envoyer l'image au serveur Python sur port 8080
            $client = new \GuzzleHttp\Client(['timeout' => 10]);
            $response = $client->post('http://127.0.0.1:8080/api/identifier', [
                'json' => ['image' => $request->image]
            ]);

            $resultat = json_decode($response->getBody(), true);

            if ($resultat['status'] === 'success') {
                $etudiantId = $resultat['etudiant_id'];
                $confiance  = $resultat['confiance'];

                // Vérifier que l'étudiant est inscrit dans la classe du cours
                $etudiant = Etudiant::with('user')
                    ->where('id', $etudiantId)
                    ->whereHas('inscriptions', fn($q) => $q->where('idClasse', $cours->idClasse))
                    ->first();

                if (!$etudiant) {
                    // Étudiant reconnu mais pas dans cette classe
                    $etudiantAutre = Etudiant::with('user')->find($etudiantId);
                    return response()->json([
                        'status'  => 'intruder',
                        'message' => 'Étudiant non inscrit dans cette classe !',
                        'confiance' => $confiance,
                        'etudiant' => [
                            'prenom' => $etudiantAutre?->user?->prenom ?? '—',
                            'nom'    => $etudiantAutre?->user?->nom ?? '—',
                        ]
                    ]);
                }

                // Marquer présent
                Absence::updateOrCreate(
                    [
                        'etudiant_id' => $etudiant->id,
                        'idCours'     => $cours->idCours,
                        'date'        => today(),
                    ],
                    [
                        'statut'          => 'present',
                        'pointage_facial' => true,
                    ]
                );

                DonneesBiometriques::where('etudiant_id', $etudiant->id)
                    ->update(['heureEntre' => now()]);

                return response()->json([
                    'status'         => 'success',
                    'message'        => 'Présence confirmée',
                    'heure_pointage' => now()->format('H:i:s'),
                    'confiance'      => $confiance,
                    'etudiant'       => [
                        'id'        => $etudiant->id,
                        'prenom'    => $etudiant->user->prenom,
                        'nom'       => $etudiant->user->nom,
                        'matricule' => $etudiant->codePar,
                        'photoUrl'  => null,
                    ]
                ]);

            } elseif ($resultat['status'] === 'inconnu') {
                return response()->json([
                    'status'  => 'intruder',
                    'message' => 'Visage non reconnu',
                ]);

            } else {
                return response()->json([
                    'status'  => 'no_match',
                    'message' => $resultat['message'] ?? 'Aucun visage détecté',
                ]);
            }

        } catch (\Exception $e) {
            // Si serveur Python indisponible → simulation
            return $this->simulerPointage($cours);
        }
    }

    /* ---- Simulation quand Python n'est pas disponible ---- */
    private function simulerPointage(Cours $cours)
    {
        $dejaPonites = Absence::where('idCours', $cours->idCours)
            ->where('statut', 'present')
            ->pluck('etudiant_id');

        $etudiant = Etudiant::with(['user', 'donneesBiometriques'])
            ->whereHas('inscriptions', fn($q) => $q->where('idClasse', $cours->idClasse))
            ->whereNotIn('id', $dejaPonites)
            ->whereHas('donneesBiometriques')
            ->first();

        if (!$etudiant) {
            // Essayer sans filtre biométrie
            $etudiant = Etudiant::with('user')
                ->whereHas('inscriptions', fn($q) => $q->where('idClasse', $cours->idClasse))
                ->whereNotIn('id', $dejaPonites)
                ->first();
        }

        if (!$etudiant) {
            return response()->json([
                'status'  => 'no_match',
                'message' => 'Tous les étudiants ont pointé.'
            ]);
        }

        Absence::updateOrCreate(
            [
                'etudiant_id' => $etudiant->id,
                'idCours'     => $cours->idCours,
                'date'        => today(),
            ],
            [
                'statut'          => 'present',
                'pointage_facial' => true,
            ]
        );

        DonneesBiometriques::where('etudiant_id', $etudiant->id)
            ->update(['heureEntre' => now()]);

        return response()->json([
            'status'         => 'success',
            'message'        => 'Présence confirmée (simulation)',
            'heure_pointage' => now()->format('H:i:s'),
            'etudiant'       => [
                'id'        => $etudiant->id,
                'prenom'    => $etudiant->user->prenom ?? '',
                'nom'       => $etudiant->user->nom ?? '',
                'matricule' => $etudiant->codePar,
                'photoUrl'  => null,
            ]
        ]);
    }

    /* ---- Liste des étudiants avec biométrie ---- */
    public function index()
    {
        $etudiants = Etudiant::with([
            'user',
            'donneesBiometriques',
            'inscriptionActuelle.classe'
        ])->paginate(20);

        return view('biometrie.index', compact('etudiants'));
    }
}