<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UE extends Model
{
    protected $table = 'ues';
    protected $primaryKey = 'idUE';
    protected $fillable = ['codeUE', 'nomUE', 'idClasse'];

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'idClasse', 'idClasse');
    }

    public function matieres()
    {
        return $this->hasMany(Matiere::class, 'idUE', 'idUE');
    }

    public function getCoefficientTotalAttribute()
    {
        return $this->matieres->sum('coefficient');
    }
}