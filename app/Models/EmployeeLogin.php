<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLogin extends Model
{
    use HasFactory;
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
    ];
}
