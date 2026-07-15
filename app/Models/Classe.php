<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    // On indique explicitement la clé primaire personnalisée
    protected $primaryKey = 'idClasse';

    protected $fillable = [
        'nom', 
        'idNiveau', 
        'idFiliere', 
        'idAnnee', 
        'effectif'
    ];

    /**
     * Relation avec l'Emploi du Temps (Créneaux)
     * Lie explicitement 'idClasse' de la table 'emplois_du_temps' 
     * avec 'idClasse' de la table 'classes'.
     */
    public function creneaux()
{
    // Indique bien la clé étrangère 'idClasse' sur la table emplois_du_temps
    return $this->hasMany(EmploiDuTemps::class, 'idClasse', 'idClasse');
}

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