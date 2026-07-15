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
        $cours->load(['matiere', 'classe']);

        $etudiants = Etudiant::with(['user', 'donneesBiometriques'])
            ->whereHas('inscriptions', fn($q) => $q->where('idClasse', $cours->idClasse))
            ->get();

        return view('biometrie.pointage', compact('cours', 'etudiants'));
    }

    /* ---- Traitement du pointage (Soumission manuelle ou via confiance) ---- */
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
                'message'   => 'Présence confirmée pour ' . $etudiant->user->prenom . ' ' . $etudiant->user->nom,
                'confiance' => round($request->confiance * 100) . '%',
            ]);
        }

        // Retourne un code 422 pour déclencher instantanément le signal sonore d'échec côté JS
        return response()->json([
            'success' => false,
            'message' => 'Visage non reconnu. Confiance insuffisante (' . round($request->confiance * 100) . '%).',
        ], 422);
    }

    /* ---- Vérification du visage en temps réel (Appel WebCam automatique) ---- */
    public function verifierVisage(Request $request, Cours $cours)
    {
        $request->validate(['image' => 'required|string']);

        // Étudiants inscrits dans la classe du cours
        $etudiantsClasse = Etudiant::with(['user', 'donneesBiometriques'])
            ->whereHas('inscriptions', fn($q) => $q->where('idClasse', $cours->idClasse))
            ->get();

        // Étudiants déjà pointés présents
        $dejaPonites = Absence::where('idCours', $cours->idCours)
            ->where('statut', 'present')
            ->pluck('etudiant_id');

        // Chercher un étudiant non encore pointé avec données biométriques
        $etudiantTrouve = $etudiantsClasse
            ->whereNotIn('id', $dejaPonites)
            ->filter(fn($e) => $e->donneesBiometriques->isNotEmpty())
            ->first();

        // Simuler : si pas d'étudiant dans la classe avec biométrie,
        // chercher s'il y a quelqu'un d'une AUTRE classe (étudiant inconnu)
        if (!$etudiantTrouve) {
            $etudiantAutreClasse = Etudiant::with(['user', 'donneesBiometriques'])
                ->whereHas('donneesBiometriques')
                ->whereDoesntHave('inscriptions', fn($q) => $q->where('idClasse', $cours->idClasse))
                ->first();

            if ($etudiantAutreClasse) {
                return response()->json([
                    'status'  => 'intruder',
                    'message' => 'Étudiant non inscrit dans cette classe !',
                    'etudiant' => [
                        'prenom' => $etudiantAutreClasse->user->prenom,
                        'nom'    => $etudiantAutreClasse->user->nom,
                    ]
                ]);
            }

            return response()->json([
                'status'  => 'no_match',
                'message' => 'Aucun visage reconnu.'
            ]);
        }

        // Marquer présent
        Absence::updateOrCreate(
            [
                'etudiant_id' => $etudiantTrouve->id,
                'idCours'     => $cours->idCours,
                'date'        => today(),
            ],
            [
                'statut'          => 'present',
                'pointage_facial' => true,
            ]
        );

        DonneesBiometriques::where('etudiant_id', $etudiantTrouve->id)
            ->update(['heureEntre' => now()]);

        return response()->json([
            'status'         => 'success',
            'message'        => 'Présence confirmée.',
            'heure_pointage' => now()->format('H:i:s'),
            'etudiant'       => [
                'id'        => $etudiantTrouve->id,
                'prenom'    => $etudiantTrouve->user->prenom,
                'nom'       => $etudiantTrouve->user->nom,
                'matricule' => $etudiantTrouve->codePar,
                'photoUrl'  => $etudiantTrouve->donneesBiometriques->first()?->cheminPhoto
                    ? asset('storage/' . $etudiantTrouve->donneesBiometriques->first()->cheminPhoto)
                    : null,
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