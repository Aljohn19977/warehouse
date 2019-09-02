<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase_Order_Item;
use App\Models\Supplier;

class Purchase_Order extends Model
{
    public $table = "purchase_order";


    protected $fillable = [
        'purchase_order_id', 
        'transaction_id', 
        'supplier_id',
        'order_date',
        'deliver_to',
        'status',
        'total',
    ];

    public function purchase_order_items(){
        return $this->hasMany(Purchase_Order_Item::Class,'purchase_order_id');
    }

    public function supplier(){
        return $this->belongsTo(Supplier::Class);
    }
}
