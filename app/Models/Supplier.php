<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    public $table = "suppliers";

    protected $fillable = [
        'supplier_id', 
        'name', 
        'address',
        'email',
        'tel_no',
        'mobile_no',
        'photo',
        'details',
        'remarks',
    ];
}
