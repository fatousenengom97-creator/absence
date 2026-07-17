<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploiDuTemps extends Model
{
    protected $table = 'emplois_du_temps';
    

protected $primaryKey = 'idEDT';
public $incrementing = true;
protected $keyType = 'int';

public function getRouteKeyName()
{
    return 'idEDT';
}

    protected $fillable = [
        'idClasse', 'professeur_id', 'idMatiere',
        'idSalle', 'jour', 'heureDebut', 'heureFin',
        'typeCours', 'couleur', 'actif'
    ];

    public function classe()
{
    return $this->belongsTo(Classe::class, 'idClasse', 'idClasse');
}

public function professeur()
{
    // Vérifie si la clé étrangère est 'professeur_id' ou 'idProfesseur'
    return $this->belongsTo(Professeur::class, 'professeur_id');
}

    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'idMatiere', 'idMatiere');
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'idSalle', 'idSalle');
    }
}