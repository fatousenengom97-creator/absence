<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse.
     * 'idClasse' a été ajouté pour éviter l'erreur SQL General error: 1364.
     */
    protected $fillable = [
        'user_id', 
        'codePar', 
        'dateNaissance', 
        'lieuNaissance',
        'idClasse',
    ];

    /**
     * Les attributs qui doivent être convertis.
     */
    protected $casts = [
        'dateNaissance' => 'date',
    ];

    /**
     * Relation avec l'utilisateur (comptes, identifiants, rôles).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation directe avec la classe de l'étudiant.
     * Nécessaire pour le chargement via ChefServiceController@alertes.
     */
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'idClasse', 'idClasse');
    }

    /**
     * Historique complet des inscriptions (toutes années confondues).
     */
    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'etudiant_id');
    }

    /**
     * Inscription de l'année académique actuellement active.
     * Permet le eager-loading : with('inscriptionActuelle.classe.filiere')
     */
    public function inscriptionActuelle()
    {
        return $this->hasOne(Inscription::class, 'etudiant_id')
            ->whereHas('anneeScolaire', fn($q) => $q->where('active', true));
    }

    /**
     * Raccourci pratique pour récupérer la classe de l'année en cours.
     * (Méthode PHP simple, à ne pas utiliser dans un with() ou un whereHas()).
     */
    public function classeActuelle()
    {
        $inscription = $this->inscriptionActuelle()->with('classe.filiere')->first();
        return $inscription?->classe;
    }

    /**
     * Données biométriques liées à l'étudiant (pour la reconnaissance faciale).
     */
    public function donneesBiometriques()
    {
        return $this->hasMany(DonneesBiometriques::class, 'etudiant_id');
    }

    /**
     * Liste de toutes les absences de l'étudiant.
     */
    public function absences()
    {
        return $this->hasMany(Absence::class, 'etudiant_id');
    }

    /**
     * Accesseur pour récupérer le nom complet de l'étudiant.
     * Sécurisé au cas où la relation avec la table users ne renvoie rien.
     */
    public function getFullNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->prenom . ' ' . $this->user->nom;
        }

        return 'Étudiant Sans Compte';
    }
    public function derniereAbsence()
    {
        return $this->hasOne(Absence::class, 'etudiant_id')->latestOfMany('date');
    }
}