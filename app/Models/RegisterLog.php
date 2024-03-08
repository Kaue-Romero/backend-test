<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'ip',
        'user_agent',
        'header',
        'query_params'
    ];
    
}
