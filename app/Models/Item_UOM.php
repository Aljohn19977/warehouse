<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item_UOM extends Model
{
    public $table = "item_uom";


    protected $fillable = [
        'acronym',
        'name'
    ];

    public function item(){
        return $this->hasOne('App\Models\Item');
    }
}
