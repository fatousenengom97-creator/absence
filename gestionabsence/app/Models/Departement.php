<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    protected $primaryKey = 'idDep';
    protected $fillable = ['nomDep'];

    public function filieres()
    {
        return $this->hasMany(Filiere::class, 'idDep', 'idDep');
    }
}