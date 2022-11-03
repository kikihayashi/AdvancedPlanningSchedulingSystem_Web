<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingMonthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_month', function (Blueprint $table) {
            $table->id();
            $table->integer('version');
            $table->integer('period');
            $table->string('item_code');
            $table->string('item_name');
            $table->integer('lot_no');
            $table->integer('month');
            $table->integer('date');
            $table->string('transport_id');
            $table->integer('number');
            $table->string('remark')->nullable();
            $table->timestamps();
            $table->unique(['version', 'period', 'month', 'date', 'item_code', 'lot_no', 'transport_id']); //讓組合不會重複
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_month');
    }
}
