<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    protected $primaryKey = 'idSalle';
    protected $fillable = ['nom', 'capacite'];

    public function cours()
    {
        return $this->hasMany(Cours::class, 'idSalle', 'idSalle');
    }
}