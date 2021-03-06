<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Receiving;


class Receiving_Item extends Model
{
    public $table = "receiving_item";

    protected $fillable = [
        'receiving_id', 
        'item_id', 
        'quantity',
        'remarks',
    ];

    public function receiving(){
        return $this->belongsTo(Receiving::Class,'receiving_id');
    }
}
