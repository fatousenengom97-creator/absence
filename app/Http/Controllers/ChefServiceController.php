<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Professeur;
use App\Models\Salle;
use App\Models\EmploiDuTemps; 
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ChefServiceController extends Controller
{
    /**
     * Affiche le tableau de bord (dashboard) du Chef de Service.
     */
    public function dashboard()
    {
        $totalClasses = Classe::count();
        $totalProfesseurs = Professeur::count();
        $totalEDT = EmploiDuTemps::count(); 

        return view('chef.dashboard', compact('totalClasses', 'totalProfesseurs', 'totalEDT')); 
    }

    /**
     * CORRECTION : Nom de méthode corrigé en "emploiDuTemps" pour correspondre à ta route.
     * Affiche l'écran d'accueil de gestion des emplois du temps (Liste des classes).
     */
    public function emploiDuTemps()
    {
        $classes = Classe::with('filiere')->orderBy('nom')->get();
        
        // On renvoie vers ta vue principale de gestion des EDT
        return view('chef.emploi-du-temps', compact('classes'));
    }

    /**
     * Affiche l'emploi du temps spécifique d'une classe.
     */
    public function edtClasse($classeId)
    {
        // 1. On récupère la classe avec sa clé primaire spécifique
        $classe = Classe::where('idClasse', $classeId)->firstOrFail();

        // 2. Récupère les créneaux de cette classe
        $creneaux = $classe->creneaux()
            ->with(['matiere', 'professeur.user', 'salle'])
            ->get();

        // 3. Regroupement par jour pour l'affichage chronologique sous forme de calendrier
        $edt = $creneaux->groupBy('jour');

        // 4. Récupère les ressources nécessaires pour le formulaire d'ajout rapide
        $matieres = Matiere::all();
        $professeurs = Professeur::with('user')->get();
        $salles = Salle::all();

        // 5. Définition des plages de l'emploi du temps
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $heures = range(8, 19); // Génère les heures de 8h à 19h

        return view('chef.edt-classe', compact(
            'classe', 
            'creneaux', 
            'edt', 
            'matieres', 
            'professeurs', 
            'salles', 
            'jours', 
            'heures'
        ));
    }

    /**
     * Enregistre un nouveau créneau de cours dans l'emploi du temps d'une classe.
     */
    public function storeEDT(Request $request, $classeId)
    {
        // On valide que la classe existe bien avant toute action
        $classe = Classe::where('idClasse', $classeId)->firstOrFail();

        // Validation stricte des données du formulaire
        $request->validate([
            'idMatiere' => 'required|exists:matieres,idMatiere',
            'professeur_id' => 'required|exists:professeurs,id',
            'idSalle' => 'required|exists:salles,idSalle',
            'jour' => 'required|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi',
            'heureDebut' => 'required',
            'heureFin' => 'required|after:heureDebut',
        ]);

        // Insertion sécurisée via la relation définie sur le modèle Classe
        $classe->creneaux()->create([
            'idMatiere' => $request->idMatiere,
            'professeur_id' => $request->professeur_id,
            'idSalle' => $request->idSalle,
            'jour' => $request->jour,
            'heureDebut' => $request->heureDebut,
            'heureFin' => $request->heureFin,
            'typeCours' => $request->input('typeCours', 'Cours'),
            'couleur' => $request->input('couleur', '#3788d8'),
            'actif' => true,
        ]);

        return redirect()->back()->with('success', 'Créneau horaire ajouté avec succès !');
    }

    /**
     * Supprime un créneau d'emploi du temps existant.
     */
    public function destroyEDT($id)
    {
        // Recherche par l'identifiant unique de la table des emplois du temps
        $creneau = EmploiDuTemps::findOrFail($id);
        $creneau->delete();

        return redirect()->back()->with('success', 'Créneau supprimé avec succès !');
    }

    /**
     * Affiche l'occupation en temps réel et l'état des salles de cours pour la journée.
     */
    public function salles()
    {
        // 1. Traduction du jour de la semaine Carbon vers le format de ta BDD
        $joursTraduits = [
            'Monday'    => 'Lundi',
            'Tuesday'   => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday'  => 'Jeudi',
            'Friday'    => 'Vendredi',
            'Saturday'  => 'Samedi',
            'Sunday'    => 'Dimanche',
        ];
        
        $nomJourAnglais = Carbon::now()->format('l'); 
        $jourActuel = $joursTraduits[$nomJourAnglais] ?? 'Lundi';

        // 2. On récupère toutes les structures de salles
        $salles = Salle::all();

        // 3. Récupération optimisée du planning lié au jour J
        $edtJour = EmploiDuTemps::with(['matiere', 'professeur.user', 'classe'])
            ->where('jour', $jourActuel)
            ->where('actif', true)
            ->get()
            ->groupBy('idSalle');

        return view('chef.salles', compact('salles', 'jourActuel', 'edtJour'));
    }

    /**
     * Calcule et génère le rapport global d'absentéisme par classe (Visualisation HTML ou Export PDF).
     */
    public function rapportGlobal(Request $request)
    {
        // 1. Récupérer toutes les classes avec leurs relations indispensables
        $classesRaw = Classe::with(['filiere', 'niveau'])->get();

        $classes = $classesRaw->map(function ($classe) {
            // Extraction des fiches de présences/absences indexées sur cette classe via les séances de cours
            $pointages = Absence::whereHas('cours', function ($query) use ($classe) {
                $query->where('idClasse', $classe->idClasse);
            })->get();

            $total = $pointages->count();
            
            // Compteurs dynamiques selon le statut
            $presents = $pointages->where('statut', 'present')->count();
            $absents  = $pointages->where('statut', 'absent')->count();

            // Calcul précis du taux d'absentéisme (gestion division par zéro incluse)
            $taux = $total > 0 ? round(($absents / $total) * 100, 1) : 0;

            // Injection à la volée des attributs calculés pour l'affichage de ta vue Blade
            $classe->total     = $total;
            $classe->presents = $presents;
            $classe->absents  = $absents;
            $classe->taux     = $taux;

            return $classe;
        });

        // 2. Traitement d'exportation vers DomPDF si demandé dans l'URL (?format=pdf)
        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('chef.rapport_pdf', compact('classes'));
            return $pdf->stream('Rapport_Global_Absences_' . now()->format('d-m-Y') . '.pdf');
        }

        return view('chef.rapport', compact('classes'));
    }
public function alertes()
{
    $absencesModifiees = \App\Models\Absence::with([
        'etudiant.user',
        'cours.matiere',
        'cours.classe'
    ])
    ->whereIn('statut', ['justifie', 'retard', 'present'])
    ->orderByDesc('updated_at')
    ->paginate(20);

    return view('chef.alertes', compact('absencesModifiees'));
}

}