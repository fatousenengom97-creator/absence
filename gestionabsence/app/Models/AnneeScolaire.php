<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnneeScolaire extends Model
{
    protected $table = 'annees_scolaires';
    protected $primaryKey = 'idAnnee';
    protected $fillable = ['libelle', 'active'];

    public function classes()
    {
        return $this->hasMany(Classe::class, 'idAnnee', 'idAnnee');
    }

    public static function active()
    {
        return static::where('active', true)->first();
    }
}