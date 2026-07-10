<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relation avec le profil Étudiant
     */
    public function etudiant()
    {
        return $this->hasOne(Etudiant::class, 'user_id');
    }

    /**
     * Relation avec le profil Professeur
     */
    public function professeur()
    {
        return $this->hasOne(Professeur::class, 'user_id');
    }

    /**
     * Accesseur propre pour le nom complet
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
    }


    // --- Dans app/Models/User.php ---

/**
 * Raccourci pour vérifier si l'utilisateur est un étudiant
 */
public function isEtudiant(): bool
{
    return $this->role === 'etudiant';
}

/**
 * Raccourci pour vérifier si l'utilisateur est un professeur
 */
public function isProfesseur(): bool
{
    return $this->role === 'professeur';
}

/**
 * Relation avec le profil Étudiant
 */

/**
 * Relation avec le profil Professeur
 */

}