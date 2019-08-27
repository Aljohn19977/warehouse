<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Weight_UOM extends Model
{
    public $table = "weight_uom";


    protected $fillable = [
        'acronym',
        'name'
    ];

    public function item(){
        return $this->hasOne('App\Models\Item');
    }
}
