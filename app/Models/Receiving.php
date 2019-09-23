<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Receiving_Item;
use App\Models\Receiving_Damage_Item;
use App\Models\Receiving_Missing_Item;
use App\Models\Purchase_Order;

class Receiving extends Model
{
    public $table = "receiving";


    protected $fillable = [
        'receiving_id', 
        'transaction_id', 
        'supplier_id',
        'order_date',
        'status',
        'total',
    ];

    public function received_items(){
        return $this->hasMany(Receiving_Item::Class,'receiving_id','receiving_id');
    }

    public function received_damage_items(){
        return $this->hasMany(Receiving_Damage_Item::Class,'receiving_id','receiving_id');
    }

    public function received_missing_items(){
        return $this->hasMany(Receiving_Missing_Item::Class,'receiving_id','receiving_id');
    }

    public function purchase_order(){
        return $this->hasOne(Purchase_Order::Class,'transaction_id','transaction_id');
    }
}
