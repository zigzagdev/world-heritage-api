<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
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
}