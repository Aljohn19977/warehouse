<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryBatchTrackedItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_batch_tracked_item', function (Blueprint $table) {
			$table->bigIncrements('id');
            $table->string('batch_tracked_item_id')->nullable();
            $table->string('receiving_item_id');
            $table->string('item_id');
            $table->string('price');
            $table->integer('quantity');
            $table->boolean('bar_code');
            $table->string('status');
            $table->string('location');
            $table->string('bin_id')->nullable();
            $table->string('pallet_id')->nullable();
            $table->string('expiration_date')->nullable();
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
        Schema::dropIfExists('inventory_batch_tracked_item');
    }
}
