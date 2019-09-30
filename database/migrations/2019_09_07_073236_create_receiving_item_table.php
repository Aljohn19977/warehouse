<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceivingItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receiving_item', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('purchase_order_item_id');
            $table->string('receiving_id');
            $table->string('item_id');
            $table->string('status')->nullable();
            $table->string('location');
            $table->integer('quantity');
            $table->boolean('bar_code');
            $table->string('price');
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
        Schema::dropIfExists('receiving_item');
    }
}
