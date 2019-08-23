<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public $table = "items";

    
    protected $fillable = [
        'item_id', 
        'name', 
        'weight',
        'weight_uom',
        'low_stock',
        'item_uom',
        'category_id',
        'description',
        'photo'
    ];

    public function supplier(){
        return $this->belongsToMany('App\Models\Supplier');
    }
}
