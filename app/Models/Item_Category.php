<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item_Category extends Model
{
    public $table = "item_category";

    protected $fillable = [
        'name', 
    ];

    public function items(){
        return $this->belongsToMany('App\Models\Item');
    }

}
