<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterLog extends Model
{
    use HasFactory;

    protected $table = 'register_log';

    protected $fillable = [
        'ip',
        'user_agent',
        'header',
        'query_params'
    ];

    protected $hidden = [
        'id',
        'redirect_id',
    ];

    public function register()
    {
        return $this->belongsTo(Register::class);
    }
}
