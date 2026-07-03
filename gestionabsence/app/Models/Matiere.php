<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    protected $table = 'matieres';
    protected $primaryKey = 'idMatiere';
    protected $fillable = ['codeMatiere', 'nomMatiere', 'cm', 'td', 'tp', 'coefficient', 'idUE'];

    // Une matière (EC) appartient à une Unité d'Enseignement
    public function ue()
    {
        return $this->belongsTo(UE::class, 'idUE', 'idUE');
    }
}