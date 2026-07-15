<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professeur extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'matricule', 'specialite'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'professeur_matiere', 'professeur_id', 'idMatiere');
    }

    public function cours()
    {
        return $this->hasMany(Cours::class, 'professeur_id');
    }

    public function getFullNameAttribute(): string
    {
        return $this->user->full_name;
    }
}