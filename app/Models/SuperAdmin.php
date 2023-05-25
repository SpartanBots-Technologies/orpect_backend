<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class SuperAdmin extends Model
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
