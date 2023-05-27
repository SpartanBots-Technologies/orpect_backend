<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class SuperAdmin extends Authenticatable implements AuthenticatableContract
{
    use HasApiTokens, HasFactory;
    public $table = 'super_admins';

    protected $fillable = [
        'fullname',
        'phone',
        'email',
        'password',
        'image',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'is_master',
    ];
}
