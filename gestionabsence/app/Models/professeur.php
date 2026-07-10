<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professeur extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'matricule', 'specialite'];

    /**
     * Relation avec le compte User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'professeur_matiere', 'professeur_id', 'idMatiere');
    }

    public function cours()
    {
        return $this->hasMany(Cours::class, 'professeur_id');
    }

    /**
     * Récupère le nom du prof depuis la table users
     */
    public function getFullNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->full_name;
        }
        return 'Professeur Anonyme';
    }
}