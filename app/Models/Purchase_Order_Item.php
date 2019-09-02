<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase_Order;

class Purchase_Order_Item extends Model
{
    public $table = "purchase_order_item";

    protected $fillable = [
        'purchase_order_id', 
        'item_id', 
        'quantity',
        'price',
        'subtotal',
    ];

    public function purchase_order(){
        return $this->belongsTo(Purchase_Order::Class);
    }

    public function item(){
        return $this->belongsToMany('App\Models\Item');
    }
}
