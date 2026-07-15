<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscription extends Model
{
    protected $primaryKey = 'idInscription';
    protected $fillable = ['etudiant_id', 'idClasse', 'idAnnee'];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'idClasse', 'idClasse');
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class, 'idAnnee', 'idAnnee');
    }
}