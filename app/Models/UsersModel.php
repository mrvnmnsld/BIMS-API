<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersModel extends Model
{
    use HasFactory;

    protected $table = 'user_tbl';
    protected $hidden = ['password','updated_at'];

    protected $guarded = [];
}
