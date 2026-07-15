<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    protected $primaryKey = 'idMatiere';

    protected $fillable = [
        'nomMatiere',
        'codeUE',
        'attribut_coefficient'
    ];

    public function professeurs()
    {
        return $this->belongsToMany(
            Professeur::class,
            'professeur_matiere',
            'idMatiere',
            'professeur_id'
        );
    }

    public function cours()
    {
        return $this->hasMany(Cours::class, 'idMatiere', 'idMatiere');
    }
    public function index()
{
    $matieres = Matiere::all();
    return view('matieres.index', compact('matieres'));
}
}