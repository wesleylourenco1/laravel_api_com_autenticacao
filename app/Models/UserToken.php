<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'revoked',
        'expires_at',
    ];

    protected $casts = [
        'revoked' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Relacionamento com o usuário dono do token
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se o token está ativo (não revogado e não expirado)
     */
    public function isActive(): bool
    {
        return !$this->revoked && (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Revoga o token
     */
    public function revoke(): void
    {
        $this->revoked = true;
        $this->save();
    }

    /**
     * Define uma expiração para o token (em minutos)
     */
    public function setExpiration(int $minutes): void
    {
        $this->expires_at = Carbon::now()->addMinutes($minutes);
        $this->save();
    }
}
