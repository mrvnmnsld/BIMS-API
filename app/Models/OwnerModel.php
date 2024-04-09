<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerModel extends Model
{
    use HasFactory;

    protected $table = 'owner_tbl';

    protected $hidden = ['fname', 'mname', 'lname'];
    protected $guarded = [];



    public function getFullNameAttribute()
    {
        return "{$this->fname} {$this->mname} {$this->lname}";
    }

    // Additional attributes to append
    protected $appends = ['full_name'];
}
