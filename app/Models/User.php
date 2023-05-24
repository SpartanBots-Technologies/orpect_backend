<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_name',
        'company_type',
        'full_name',
        'designation',
        'domain_name',
        'email',
        'password',
        'image',
        'email_verified',
        'coupon',
        'terms_and_conditions',
        'is_deleted',
        'deleted_by',
        'company_phone',	
        'webmaster_email',	
        'company_address',	
        'company_city',	
        'company_state',	
        'company_country',	
        'company_postal_code',	
        'registration_number',	
        'company_social_link',	
        'is_account_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
