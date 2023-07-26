<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;


class EmployeeLogin extends Authenticatable implements AuthenticatableContract
{
    use HasApiTokens, HasFactory;
    public $table = 'employee_logins';

    protected $fillable = [
        'fullname',
        'username',
        'gender',
        'phone',
        'email',
        'password',
        'profile_image',
        'date_of_birth',
        'tax_number',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'linked_in',
        'taken_membership',
        'is_deleted',
        'deleted_at',
        'is_verified',
    ];
}
