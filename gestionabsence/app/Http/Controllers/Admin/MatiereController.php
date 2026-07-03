<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departement;
use App\Models\Matiere;
use Illuminate\Http\Request;

class MatiereController extends Controller
{
    /**
     * Affiche la liste des matières structurée par Département > Filière > Classe > Semestre.
     */
    public function index()
    {
        // 1. On récupère la hiérarchie complète depuis la base de données
        $departements = Departement::with(['filieres.classes.ues.matieres'])->get();

        // 2. Cloisonnement dynamique par semestre pour chaque niveau et filière
        foreach ($departements as $departement) {
            foreach ($departement->filieres as $filiere) {
                foreach ($filiere->classes as $classe) {
                    
                    // On trie et regroupe les UEs de la classe sous une collection "semestres"
                    $classe->semestres = $classe->ues->groupBy(function ($ue) {
                        $code = strtoupper($ue->codeUE);

                        // --- CAS DE LA FILIÈRE D2A (Basé sur les blocs de la maquette) ---
                        if (str_contains($code, '11')) return 'Semestre 1'; // ex: WEB111
                        if (str_contains($code, '12')) return 'Semestre 2'; // ex: WEB121
                        if (str_contains($code, '23')) return 'Semestre 3'; // ex: WEB231
                        if (str_contains($code, '24')) return 'Semestre 4'; // ex: WEB241
                        if (str_contains($code, '35')) return 'Semestre 5'; // ex: WEB351
                        if (str_contains($code, '36')) return 'Semestre 6'; // ex: WEB361

                        // --- CAS GÉNÉRAL ET FILIÈRE SRT (Extraction du premier chiffre du bloc numérique) ---
                        // ex: SRT121 -> Contient "12" au début du bloc numérique -> Semestre 2
                        // ex: SRT243 -> Contient "24" au début du bloc numérique -> Semestre 4
                        preg_match('/\d+/', $code, $matches);
                        if (!empty($matches)) {
                            $digits = $matches[0]; // Extrait les chiffres (ex: 121, 243)
                            
                            if (str_starts_with($digits, '11')) return 'Semestre 1';
                            if (str_starts_with($digits, '12')) return 'Semestre 2';
                            if (str_starts_with($digits, '23')) return 'Semestre 3';
                            if (str_starts_with($digits, '24')) return 'Semestre 4';
                            if (str_starts_with($digits, '35')) return 'Semestre 5';
                            if (str_starts_with($digits, '36')) return 'Semestre 6';
                        }

                        // Sécurité si un code ne correspond à aucun standard connu
                        return 'Semestre Général';
                    });

                    // Optionnel : Trier les semestres par clé (Semestre 1, Semestre 2...) pour l'affichage de la vue
                    $classe->semestres = $classe->semestres->sortKeys();
                }
            }
        }

        // 3. Pagination globale pour l'administration générale si nécessaire
        $matieres = Matiere::paginate(10); 

        // 4. Envoi des variables structurées à ta vue Blade
        return view('admin.matieres.index', compact('departements', 'matieres'));
    }

    /**
     * Enregistre une nouvelle matière.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codeMatiere' => 'required|unique:matieres,codeMatiere',
            'nomMatiere'  => 'required',
            'idUE'        => 'required|exists:ues,idUE',
            'coefficient' => 'required|integer|min:1',
        ]);

        Matiere::create($request->all());

        return redirect()->back()->with('success', 'Matière ajoutée avec succès !');
    }

    /**
     * Supprime une matière spécifique.
     */
    public function destroy($id)
    {
        $matiere = Matiere::findOrFail($id);
        $matiere->delete();

        return redirect()->back()->with('success', 'Matière supprimée avec succès !');
    }
}