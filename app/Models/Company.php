<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase_Order;


class Company extends Model
{
    public $table = "companies";


    protected $fillable = [
        'company_id', 
        'address',
        'email',
        'tel_no',
        'mobile_no',
        'photo',
        'details',
        'remarks',
    ];

    public function supplier(){
        return $this->belongsToMany('App\Models\Supplier');
    }

    public function item(){
        return $this->hasMany('App\Models\Item_Category');
    }

}
