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
            'review',
            'added_by',
            'is_deleted',
            'date_of_birth',
            'emp_pan',
            'permanent_address',
            'city',
            'country',
            'state',
            'postal_code',
            'linked_in',
            'status_changed_at',
            'overall_rating',
            'performance_rating',
            'professional_skills_rating',
            'teamwork_communication_rating',
            'attitude_behaviour_rating',
            'last_CTC',
    ];
}
