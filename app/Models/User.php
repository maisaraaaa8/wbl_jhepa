<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;


    protected $table = 'profiles';


    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';


    protected $fillable = [
        'id',
        'email',
        'password',
        'role',
        'nama',       // tambah ni
        'no_matrik',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];
}