<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    protected $primaryKey = 'idCours';
    protected $fillable = [
        'idMatiere', 'professeur_id', 'idClasse',
        'idSalle', 'heureDebut', 'heureFin', 'jour',
        'statut', 'typeCours',
    ];
    protected $casts = [
        'heureDebut' => 'datetime',
        'heureFin'   => 'datetime',
    ];

    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'idMatiere', 'idMatiere');
    }
    public function professeur()
    {
        return $this->belongsTo(Professeur::class, 'professeur_id');
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