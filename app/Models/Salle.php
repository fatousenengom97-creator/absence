<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    protected $primaryKey = 'idSalle';
    protected $fillable = ['nom', 'capacite'];

    public function cours()
    {
        return $this->hasMany(Cours::class, 'idSalle', 'idSalle');
    }

    // Vérifie si la salle est occupée à un moment donné
    public function estOccupee($debut, $fin)
    {
        return $this->cours()
            ->where('statut', '!=', 'annule')
            ->where('heureDebut', '<', $fin)
            ->where('heureFin', '>', $debut)
            ->exists();
    }
}