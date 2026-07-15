<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrateur extends Model
{
    protected $table = 'administrateurs';
    protected $fillable = ['user_id', 'niveauAcces'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}