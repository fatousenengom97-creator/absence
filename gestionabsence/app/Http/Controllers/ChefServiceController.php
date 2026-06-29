<?php

namespace App\Http\Controllers;

use App\Models\{Absence, Classe, Cours, Etudiant, Professeur};
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ChefServiceController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'etudiants'        => Etudiant::count(),
            'professeurs'      => Professeur::count(),
            'cours_aujourdhui' => Cours::whereDate('heureDebut', today())->count(),
            'absences_mois'    => Absence::whereMonth('date', now()->month)->where('statut', 'absent')->count(),
        ];

        $alertes = Etudiant::with('user')
            ->withCount(['absences as nb_absences' => fn($q) => $q->where('statut', 'absent')])
            ->having('nb_absences', '>', 5)
            ->orderByDesc('nb_absences')
            ->take(10)->get();

        return view('chef.dashboard', compact('stats', 'alertes'));
    }

    public function rapportGlobal(Request $request)
    {
        $classes = Classe::with(['filiere', 'niveau'])->get()->map(function ($classe) {
            $total   = Absence::whereHas('cours', fn($q) => $q->where('idClasse', $classe->idClasse))->count();
            $absents = Absence::whereHas('cours', fn($q) => $q->where('idClasse', $classe->idClasse))
                ->where('statut', 'absent')->count();
            $classe->total    = $total;
            $classe->absents  = $absents;
            $classe->presents = $total - $absents;
            $classe->taux     = $total > 0 ? round(($absents / $total) * 100, 1) : 0;
            return $classe;
        });

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('chef.rapport_pdf', compact('classes'))->setPaper('a4', 'landscape');
            return $pdf->stream('rapport_global.pdf');
        }

        return view('chef.rapport', compact('classes'));
    }

    public function alertes()
    {
        $etudiants = Etudiant::with(['user', 'classe.filiere'])
            ->withCount(['absences as nb_absences' => fn($q) => $q->where('statut', 'absent')])
            ->having('nb_absences', '>', 3)
            ->orderByDesc('nb_absences')
            ->paginate(20);

        return view('chef.alertes', compact('etudiants'));
    }
}