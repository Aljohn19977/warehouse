<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory_Item extends Model
{
    public $table = "inventory_item";

    public function item(){
        return $this->hasOne('App\Models\Item','id','item_id');
    }
}
