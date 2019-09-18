<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrderItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_item', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('purchase_order_id');
            $table->string('item_id');
            $table->string('type');
            $table->string('cubic');
            $table->string('weight_uom_id');
            $table->string('weight');
            $table->integer('quantity');
            $table->string('price');
            $table->string('line_total');
            $table->string('tax');
            $table->string('tax_total');  
            $table->string('subtotal');
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_order_item');
    }
}
