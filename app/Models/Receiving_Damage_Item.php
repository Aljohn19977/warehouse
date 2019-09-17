<?php

namespace App\Models;
use App\Models\Receiving;

use Illuminate\Database\Eloquent\Model;

class Receiving_Damage_Item extends Model
{
    public $table = "receiving_damage_item";

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
