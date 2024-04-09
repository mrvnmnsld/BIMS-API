<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelModel extends Model
{
    use HasFactory;

    protected $table = 'travel_tbl';
    protected $hidden = ['updated_at'];
    protected $guarded = [];

    public function boats()
    {
        return $this->belongsTo(BoatsModel::class, 'boat_id', 'id');
    }

}
