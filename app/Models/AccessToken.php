<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model
{
    protected $fillable = [
        'client_id', 'token', 'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',  // <- Adicione isto
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function isExpired()
    {
        return now()->greaterThan($this->expires_at);
    }
}
