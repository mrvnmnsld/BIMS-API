<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BoatsModel extends Model
{
    use HasFactory;
    protected $table = 'boat_tbl';
    protected $hidden = ['owner_id'];
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // In BoatsModel.php
    // Define the relationship with TypesModel
    public function type()
    {
        return $this->belongsTo(TypesModel::class, 'type', 'id');
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }

    // Define the relationship with OwnerModel
    // public function owner()
    // {
    //     return $this->belongsTo(OwnerModel::class, 'owner_id', 'id');
    // }

    public function papers()
    {
        return $this->belongsTo(BoatPapersModel::class, 'id', 'boat_id');
    }

    public function destination()
    {
        return $this->belongsTo(DestinationModel::class, 'id', 'boat_id');
    }

    
}
