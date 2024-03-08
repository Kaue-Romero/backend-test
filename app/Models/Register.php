<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Register extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'register';

    protected $fillable = [
        'status',
        'url',
        'last_access'
    ];

    protected $cast = [
        'last_access' => 'datetime',
        'status' => 'boolean'
    ];
}