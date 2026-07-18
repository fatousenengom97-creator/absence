<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Professeur;
use App\Models\Salle;
use App\Models\EmploiDuTemps; 
use App\Models\Etudiant;
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
     * Affiche l'écran d'accueil de gestion des emplois du temps (Liste des classes).
     */
    public function emploiDuTemps()
    {
        $classes = Classe::with('filiere')->orderBy('nom')->get();
        
        return view('chef.emploi-du-temps', compact('classes'));
    }

    /**
     * Affiche l'emploi du temps spécifique d'une classe.
     */
    public function edtClasse($classeId)
    {
        $classe = Classe::where('idClasse', $classeId)->firstOrFail();

        // Récupère les créneaux de cette classe via la clé idClasse
        $creneaux = EmploiDuTemps::where('idClasse', $classeId)
            ->with(['matiere', 'professeur.user', 'salle'])
            ->get();

        $edt = $creneaux->groupBy('jour');

        $matieres = Matiere::all();
        $professeurs = Professeur::with('user')->get();
        $salles = Salle::all();

        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $heures = range(8, 19); 

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
        $classe = Classe::where('idClasse', $classeId)->firstOrFail();

        $request->validate([
            'idMatiere' => 'required|exists:matieres,idMatiere',
            'professeur_id' => 'required|exists:professeurs,id',
            'idSalle' => 'required|exists:salles,idSalle',
            'jour' => 'required|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi',
            'heureDebut' => 'required',
            'heureFin' => 'required|after:heureDebut',
        ]);

        EmploiDuTemps::create([
            'idClasse' => $classe->idClasse,
            'idMatiere' => $request->idMatiere,
            'professeur_id' => $request->professeur_id,
            'idSalle' => $request->idSalle,
            'jour' => $request->jour,
            'heureDebut' => $request->heureDebut,
            'heureFin' => $request->heureFin,
            'typeCours' => $request->input('typeCours', 'CM'),
            'couleur' => $request->input('couleur', '#3B82F6'),
            'actif' => true,
        ]);

        return redirect()->back()->with('success', 'Créneau horaire ajouté avec succès !');
    }

    /**
     * Supprime un créneau d'emploi du temps existant via sa clé primaire idEDT.
     */
    public function destroyEDT($idEDT)
    {
        // Recherche par l'identifiant unique personnalisé idEDT
        $creneau = EmploiDuTemps::where('idEDT', $idEDT)->firstOrFail();
        $creneau->delete();

        return redirect()->back()->with('success', 'Créneau supprimé avec succès !');
    }

    /**
     * Affiche l'occupation en temps réel et l'état des salles de cours pour la journée.
     */
    public function salles()
    {
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

        $salles = Salle::all();

        $edtJour = EmploiDuTemps::with(['matiere', 'professeur.user', 'classe'])
            ->where('jour', $jourActuel)
            ->where('actif', true)
            ->get()
            ->groupBy('idSalle');

        return view('chef.salles', compact('salles', 'jourActuel', 'edtJour'));
    }

    /**
     * Calcule et génère le rapport global d'absentéisme par classe.
     */
    public function rapportGlobal(Request $request)
    {
        $classesRaw = Classe::with(['filiere', 'niveau'])->get();

        $classes = $classesRaw->map(function ($classe) {
            $pointages = Absence::whereHas('cours', function ($query) use ($classe) {
                $query->where('idClasse', $classe->idClasse);
            })->get();

            $total = $pointages->count();
            $presents = $pointages->where('statut', 'present')->count();
            $absents  = $pointages->where('statut', 'absent')->count();

            $taux = $total > 0 ? round(($absents / $total) * 100, 1) : 0;

            $classe->total = $total;
            $classe->presents = $presents;
            $classe->absents  = $absents;
            $classe->taux = $taux;

            return $classe;
        });

        return view('chef.rapport', compact('classes'));
    }

    /**
     * Génère l'export PDF du rapport global pour le Chef de Service.
     */
    public function genererRapportPDF()
    {
        $classesRaw = Classe::with(['filiere', 'niveau'])->get();

        $classes = $classesRaw->map(function ($classe) {
            $pointages = Absence::whereHas('cours', function ($query) use ($classe) {
                $query->where('idClasse', $classe->idClasse);
            })->get();

            $total = $pointages->count();
            $absents = $pointages->where('statut', 'absent')->count();
            $classe->taux = $total > 0 ? round(($absents / $total) * 100, 1) : 0;
            $classe->absents = $absents;

            return $classe;
        });

        $pdf = Pdf::loadView('chef.rapport_pdf', compact('classes'));
        return $pdf->stream('Rapport_Global_SATIC_' . now()->format('d-m-Y') . '.pdf');
    }

    /**
     * Alerte automatique quand un étudiant dépasse le seuil d'absences autorisé (ex: >= 3).
     */
public function alertes()
{
    $etudiantsEnAlerte = Etudiant::with(['inscriptionActuelle.classe.filiere', 'user'])
        ->withCount(['absences' => function ($query) {
            $query->where('statut', 'absent');
        }])
        ->having('absences_count', '>=', 5) // seuil : 5 absences ou plus
        ->get();

    return view('admin.etudiants_filieres.alertes', compact('etudiantsEnAlerte'));
}
}