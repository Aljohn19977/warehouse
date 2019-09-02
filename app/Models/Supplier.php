<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase_Order;

class Supplier extends Model
{
    public $table = "suppliers";

    protected $fillable = [
        'supplier_id',
        'company_id', 
        'name', 
        'address',
        'email',
        'tel_no',
        'mobile_no',
        'photo',
        'details',
        'remarks',
    ];

    
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }

    public function item(){
        return $this->belongsToMany('App\Models\Item');
    }

    public function purchase_order(){
        return $this->hasMany(Purchase_Order::Class);
    }
}
