<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Seance extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_cours', // ou nomCours selon ta base
        'idProfesseur',
        'idClasse',
        'salle',
        'date_cours',
        'heure_debut',
        'heure_fin',
        'statut',
        'heure_demarrage_reel',
        'heure_cloture_reelle',
    ];

    // Relation avec la classe (si non présente)
    public function SampleClasse()
    {
        return $this->belongsTo(Classe::class, 'idClasse');
    }

    /**
     * Vérifie si le pointage par reconnaissance faciale est toujours actif
     */
    public function pointageEstOuvert(): bool
    {
        if ($this->statut !== 'en_cours' || !$this->heure_demarrage_reel) {
            return false;
        }

        $demarrage = Carbon::parse($this->heure_demarrage_reel);
        
        // Moins de 30 minutes d'écart entre l'heure de démarrage et maintenant
        return Carbon::now()->lessThanOrEqualTo($demarrage->addMinutes(30));
    }
}