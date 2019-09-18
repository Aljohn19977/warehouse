<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('purchase_order_id');
            $table->string('transaction_id');
            $table->string('supplier_id');
            $table->string('order_date');
            $table->string('deliver_to');
            $table->string('total_volume');
            $table->string('total_weight');
            $table->string('total_tax');
            $table->string('subtotal');
            $table->string('total');
            $table->string('comments');
            $table->string('status');
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
        Schema::dropIfExists('purchase_order');
    }
}
