<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('management', function (Blueprint $table) {
            $table->id();
            $table->integer('version');
            $table->integer('period');
            $table->string('item_code');
            $table->string('item_name');
            $table->string('eternal_code');
            $table->string('stock_code');
            $table->integer('lot_no');
            $table->integer('lot_total');
            $table->string('order_no');
            $table->string('batch');
            $table->string('transport_id');
            $table->string('remark_transport')->nullable();
            $table->string('remark_other')->nullable(); 
            $table->string('arrival_date');
            $table->string('shipment_date');
            $table->string('actual_date')->nullable(); 
            $table->string('product_date');
            $table->string('material_date');
            $table->integer('month_4');
            $table->integer('month_5');
            $table->integer('month_6');
            $table->integer('month_7');
            $table->integer('month_8');
            $table->integer('month_9');
            $table->integer('month_10');
            $table->integer('month_11');
            $table->integer('month_12');
            $table->integer('month_1');
            $table->integer('month_2');
            $table->integer('month_3');     
            $table->timestamps();
            $table->unique(['period','item_code', 'lot_no', 'version']);//讓組合不會重複
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('management');
    }
}
