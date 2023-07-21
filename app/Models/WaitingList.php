<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitingList extends Model
{
    use HasFactory;
    public $table = 'waiting_lists';
    protected $fillable =[
        'name',
        'company_name',
        'email',
        'phone',
    ];
}
