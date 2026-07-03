<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UE extends Model
{
    protected $table = 'ues';
    protected $primaryKey = 'idUE';
    protected $fillable = ['codeUE', 'libelle', 'credits', 'idClasse'];

    // Une UE appartient à une classe (ex: L3 D2A)
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'idClasse', 'idClasse');
    }

    // Une UE regroupe plusieurs éléments constitutifs (matières)
    public function matieres()
    {
        return $this->hasMany(Matiere::class, 'idUE', 'idUE');
    }
}