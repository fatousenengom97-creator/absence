<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $primaryKey = 'idPresence';
    protected $fillable = [
        'etudiant_id', 'idCours', 'date',
        'statut', 'justification', 'pointage_facial',
    ];

    protected $casts = [
        'date'           => 'date',
        'pointage_facial' => 'boolean',
    ];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_id');
    }

    public function cours()
    {
        return $this->belongsTo(Cours::class, 'idCours', 'idCours');
    }
}
