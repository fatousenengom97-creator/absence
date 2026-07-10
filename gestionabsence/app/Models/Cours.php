<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    // On spécifie explicitement la table vue sur phpMyAdmin
    protected $table = 'cours';

    protected $primaryKey = 'idCours';
    
    protected $fillable = [
        'jour',
        'heureDebut',
        'heureFin',
        'idClasse',
        'idMatiere',
        'professeur_id',
        'idSalle',
        'typeCours', // Ajouté car présent dans ta table phpMyAdmin
        'statut',    // Ajouté car présent dans ta table phpMyAdmin
    ];

    // ⚠️ On commente temporairement les casts pour éviter que Laravel ne casse le format de l'heure reçu par le formulaire avant l'insertion SQL
    protected $casts = [
        // 'heureDebut' => 'datetime',
        // 'heureFin'   => 'datetime',
    ];

    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'idMatiere', 'idMatiere');
    }

    public function professeur()
    {
        // Si tes professeurs sont directement dans la table 'users' :
        return $this->belongsTo(User::class, 'professeur_id');
        
        // Si tu as vraiment un modèle indépendant appelé Professeur, remets cette ligne :
        // return $this->belongsTo(Professeur::class, 'professeur_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'idClasse', 'idClasse');
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'idSalle', 'idSalle');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class, 'idCours', 'idCours');
    }
}