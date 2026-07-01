<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonneesBiometriques extends Model
{
    protected $table = 'donnees_biometriques';
    protected $primaryKey = 'idBiometrie';
    protected $fillable = [
        'etudiant_id', 'faceVector', 'cheminPhoto',
        'dateEnregistre', 'heureEntre',
    ];

    protected $casts = [
        'dateEnregistre' => 'datetime',
        'heureEntre'     => 'datetime',
    ];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_id');
    }
}
