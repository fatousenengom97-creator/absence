<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RapportCours extends Model
{
    protected $table = 'rapports_cours';

    protected $fillable = ['idCours', 'lu'];

    public function cours()
    {
        return $this->belongsTo(Cours::class, 'idCours', 'idCours');
    }
}