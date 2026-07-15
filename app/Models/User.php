<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom', 'prenom', 'telephone', 'adresse',
        'email', 'password', 'role', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // ---- Relations One-to-One standard (Une table par rôle) ----

    public function etudiant()
    {
        return $this->hasOne(Etudiant::class);
    }

   public function professeur()
{
    // Assure-toi que la clé étrangère correspond (ex: 'user_id' ou 'idUser')
    return $this->hasOne(Professeur::class, 'user_id'); 
}

    public function administrateur()
    {
        return $this->hasOne(Administrateur::class);
    }

    public function chefService()
    {
        return $this->hasOne(ChefService::class, 'user_id');
    }

    // ---- Helpers de rôle ----

    public function isAdmin(): bool
    {
        return $this->role === 'administrateur';
    }

    public function isEtudiant(): bool
    {
        return $this->role === 'etudiant';
    }

    public function isProfesseur(): bool
    {
        return $this->role === 'professeur';
    }

    public function isChefService(): bool
    {
        return $this->role === 'chef_service';
    }

    /**
     * Vérifie si l'utilisateur possède un rôle spécifique.
     */
    public function hasRole($role): bool
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }

        return $this->role === $role;
    }

    /**
     * Accesseur pour le nom complet
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }
}