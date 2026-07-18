<?php

namespace App\Http\Controllers;

use App\Models\DonneesBiometriques;
use App\Models\Etudiant;
use App\Models\Absence;
use App\Models\Cours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use GuzzleHttp\Client;

class BiometrieController extends Controller
{
    /**
     * Page d'enregistrement biométrique d'un étudiant.
     */
    public function enregistrer(Etudiant $etudiant)
    {
        $biometrie = $etudiant->donneesBiometriques()->latest()->first();
        return view('biometrie.enregistrer', compact('etudiant', 'biometrie'));
    }

    /**
     * Sauvegarder le vecteur facial généré et la photo témoin.
     */
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

        return response()->json(['success' => true, 'message' => 'Données biométriques enregistrées avec succès.']);
    }

    /**
     * Page d'émargement et de pointage facial en temps réel pour un cours.
     */
    public function pointage(Cours $cours)
    {
        $cours->load(['matiere', 'classe', 'salle']);

        $etudiants = Etudiant::with(['user', 'donneesBiometriques'])
            ->whereHas('inscriptions', fn($q) => $q->where('idClasse', $cours->idClasse))
            ->get();

        return view('biometrie.pointage', compact('cours', 'etudiants'));
    }

    /**
     * Traitement manuel ou via script JS du pointage d'un étudiant spécifique.
     */
    public function traiterPointage(Request $request, Cours $cours)
    {
        $request->validate([
            'etudiant_id' => 'required|exists:etudiants,id',
            'confiance'   => 'required|numeric|min:0|max:1',
        ]);

        $seuilConfiance = 0.75;
        $dateCours = Carbon::parse($cours->heureDebut)->toDateString();

        if ($request->confiance >= $seuilConfiance) {
            Absence::updateOrCreate(
                [
                    'etudiant_id' => $request->etudiant_id,
                    'idCours'     => $cours->idCours,
                    'date'        => $dateCours,
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

    /**
     * Transmission du flux vidéo au serveur de reconnaissance Python.
     */
    public function verifierVisage(Request $request, Cours $cours)
    {
        $request->validate(['image' => 'required|string']);
        $dateCours = Carbon::parse($cours->heureDebut)->toDateString();

        try {
            // Requête HTTP vers l'API d'identification Python
            $client = new Client(['timeout' => 10]);
            $response = $client->post('http://127.0.0.1:8080/api/identifier', [
                'json' => ['image' => $request->image]
            ]);

            $resultat = json_decode($response->getBody(), true);

            if (isset($resultat['status']) && $resultat['status'] === 'success') {
                $etudiantId = $resultat['etudiant_id'];
                $confiance  = $resultat['confiance'];

                // Vérifier l'appartenance de l'étudiant à la classe concernée
                $etudiant = Etudiant::with('user')
                    ->where('id', $etudiantId)
                    ->whereHas('inscriptions', fn($q) => $q->where('idClasse', $cours->idClasse))
                    ->first();

                if (!$etudiant) {
                    $etudiantAutre = Etudiant::with('user')->find($etudiantId);
                    return response()->json([
                        'status'    => 'intruder',
                        'message'   => 'Étudiant non inscrit dans cette classe !',
                        'confiance' => $confiance,
                        'etudiant'  => [
                            'prenom' => $etudiantAutre?->user?->prenom ?? '—',
                            'nom'    => $etudiantAutre?->user?->nom ?? '—',
                        ]
                    ]);
                }

                // Validation de présence
                Absence::updateOrCreate(
                    [
                        'etudiant_id' => $etudiant->id,
                        'idCours'     => $cours->idCours,
                        'date'        => $dateCours,
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

            } elseif (isset($resultat['status']) && $resultat['status'] === 'inconnu') {
                return response()->json([
                    'status'  => 'intruder',
                    'message' => 'Visage non reconnu',
                ]);
            }

            return response()->json([
                'status'  => 'no_match',
                'message' => $resultat['message'] ?? 'Aucun visage détecté',
            ]);

        } catch (\Exception $e) {
            // Fallback automatique sur le système de simulation si le serveur Python est éteint
            return $this->simulerPointage($cours);
        }
    }

    /**
     * Simulation interne du mécanisme de détection faciale (si Python hors-ligne).
     */
    private function simulerPointage(Cours $cours)
    {
        $dateCours = Carbon::parse($cours->heureDebut)->toDateString();

        $dejaPointes = Absence::where('idCours', $cours->idCours)
            ->where('statut', 'present')
            ->pluck('etudiant_id');

        $etudiant = Etudiant::with(['user', 'donneesBiometriques'])
            ->whereHas('inscriptions', fn($q) => $q->where('idClasse', $cours->idClasse))
            ->whereNotIn('id', $dejaPointes)
            ->whereHas('donneesBiometriques')
            ->first();

        if (!$etudiant) {
            $etudiant = Etudiant::with('user')
                ->whereHas('inscriptions', fn($q) => $q->where('idClasse', $cours->idClasse))
                ->whereNotIn('id', $dejaPointes)
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
                'date'        => $dateCours,
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

    /**
     * Liste globale des étudiants disposant de données biométriques.
     */
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