<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'codePar', 'dateNaissance', 'lieuNaissance', 'idClasse',
    ];

    protected $casts = [
        'dateNaissance' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Historique complet des inscriptions (toutes années confondues)
    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'etudiant_id');
    }

    // Inscription de l'année académique actuellement active.
    // On peut faire eager-load: with('inscriptionActuelle.classe.filiere')
    public function inscriptionActuelle()
    {
        return $this->hasOne(Inscription::class, 'etudiant_id')
            ->whereHas('anneeScolaire', fn($q) => $q->where('active', true));
    }

    // Raccourci pratique (méthode PHP simple, PAS une relation Eloquent,
    // donc ne jamais utiliser dans with()/whereHas() -- utiliser
    // 'inscriptionActuelle.classe.filiere' pour ça).
    public function classeActuelle()
    {
        $inscription = $this->inscriptionActuelle()->with('classe.filiere')->first();
        return $inscription?->classe;
    }

    public function donneesBiometriques()
    {
        return $this->hasMany(DonneesBiometriques::class, 'etudiant_id');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class, 'etudiant_id');
    }

    public function getFullNameAttribute(): string
    {
        return $this->user->full_name;
    }
}