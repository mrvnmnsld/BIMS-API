<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoatPapersModel extends Model
{
    use HasFactory;

    protected $table = 'boat_papers_tbl';
    protected $hidden = ['id','boat_id','created_at','updated_at'];
    protected $guarded = [];

}
