<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
            'emp_id',
            'emp_name',
            'email',
            'phone',
            'position',
            'date_of_joining',
            'profile_image',
            'ex_employee',
            'non_joiner',
            'date_of_leaving',
            'rating',
            'review',
            'added_by',
            'is_deleted',
    ];
}
