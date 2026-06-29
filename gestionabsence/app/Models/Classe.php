<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    protected $primaryKey = 'idClasse';
    protected $fillable = ['nom', 'idNiveau', 'idFiliere', 'idAnnee', 'effectif'];

    public function niveau()
    {
        return $this->belongsTo(Niveau::class, 'idNiveau', 'idNiveau');
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class, 'idFiliere', 'idFiliere');
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class, 'idAnnee', 'idAnnee');
    }

    public function etudiants()
    {
        return $this->hasMany(Etudiant::class, 'idClasse', 'idClasse');
    }

    public function cours()
    {
        return $this->hasMany(Cours::class, 'idClasse', 'idClasse');
    }

    public function ues()
    {
        return $this->hasMany(UE::class, 'idClasse', 'idClasse');
    }
}