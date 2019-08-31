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

    public function category(){
        return $this->belongsTo('App\Models\Item_Category','category_id');
    }

    public function uom_weight(){
        return $this->belongsTo('App\Models\Weight_UOM','weight_uom_id');
    }

    public function uom_item(){
        return $this->belongsTo('App\Models\Item_UOM','item_uom_id');
    }
}
