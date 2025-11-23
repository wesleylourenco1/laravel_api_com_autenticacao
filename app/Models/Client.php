<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
    'name', 'client_id', 'client_secret', 'active'
];


    protected $hidden = [
        'client_secret'
    ];

    public function tokens()
    {
        return $this->hasMany(AccessToken::class);
    }
}
