<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    protected $primaryKey = 'idFiliere';
    protected $fillable = ['nomFiliere', 'idDep'];

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'idDep', 'idDep');
    }

    public function classes()
    {
        return $this->hasMany(Classe::class, 'idFiliere', 'idFiliere');
    }
}