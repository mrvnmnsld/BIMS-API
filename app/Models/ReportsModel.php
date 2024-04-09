<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportsModel extends Model
{
    use HasFactory;

    protected $table = 'reports_tbl';
    // protected $hidden = ['owner_id'];
    protected $guarded = [];

    public function boats()
    {
        return $this->belongsTo(BoatsModel::class, 'boat_id', 'id');
    }
}
