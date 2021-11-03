<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $dates = ['created_at', 'updated_at', 'registered_at'];


    protected static function booted()
    {
        static::updating(function ($user) {
            if ($user->avatar != $user->getOriginal('avatar')) {
                if (Storage::exists($user->getOriginal('avatar')))
                    Storage::delete($user->getOriginal('avatar'));
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'user_name',
        'avatar',
        'email',
        'password',
        'verification_token',
        'token_used',
        'pin',
        'registered_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'registered_at' => 'datetime',
    ];
}
