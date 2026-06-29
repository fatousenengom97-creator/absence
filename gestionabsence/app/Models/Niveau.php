<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Niveau extends Model
{
    use HasFactory;

    // Définir la clé primaire personnalisée si vous n'utilisez pas "id"
    protected $primaryKey = 'idNiveau';

    // Autoriser l'assignation de masse pour le champ 'nom'
    protected $fillable = ['nom'];

    /**
     * Un niveau possède plusieurs classes (ex: L1-INFO-A, L1-TELE, etc.)
     */
    public function classes(): HasMany
    {
        return $this->hasMany(Classe::class, 'idNiveau', 'idNiveau');
    }
}