<?php

namespace App\Models;
use App\Models\Receiving;

use Illuminate\Database\Eloquent\Model;

class Receiving_Missing_Item extends Model
{
    public $table = "receiving_missing_item";

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
