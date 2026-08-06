<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    protected $table = 'users';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'age_range',
        'subscription_tier',
        'subscription_expires_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password'                => 'hashed',
            'subscription_expires_at' => 'datetime',
            'email_verified_at'       => 'datetime',
        ];
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(
            WorldHeritage::class,
            'user_favorite',
            'user_id',
            'world_heritage_site_id'
        );
    }
}