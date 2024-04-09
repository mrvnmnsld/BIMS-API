<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypesModel extends Model
{
    use HasFactory;
    protected $table = 'type_tbl';
    protected $hidden = ['updated_at','created_at'];

    protected $guarded = [];

}
