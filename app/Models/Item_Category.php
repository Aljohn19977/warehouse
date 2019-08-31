<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item_Category extends Model
{
    public $table = "item_category";

    protected $fillable = [
        'name', 
    ];

    public function item(){
        return $this->hasOne('App\Models\Item');
    }

}
