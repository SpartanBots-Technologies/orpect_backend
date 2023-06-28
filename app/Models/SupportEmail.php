<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportEmail extends Model
{
    use HasFactory;

    public $table = 'support_emails';
    protected $fillable =[
        'name',
        'email',
        'subject',
        'message',
        'replied',
        'replyMessage',
    ];
}
