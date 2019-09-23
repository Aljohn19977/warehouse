<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('item_id');
            $table->integer('category_id');
            $table->string('type');
            $table->string('name');
            $table->string('weight');
            $table->integer('min_stock');
            $table->integer('max_stock');
            $table->string('item_uom');
            $table->string('purchase_price');
            $table->string('sale_price');
            $table->string('cubic');
            $table->string('tax');
            $table->string('description')->nullable();
            $table->string('photo')->nullable();
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
        Schema::dropIfExists('items');
    }
}
