<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChefService extends Model
{
    protected $table = 'chefs_service';
    protected $fillable = ['user_id', 'poste'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}