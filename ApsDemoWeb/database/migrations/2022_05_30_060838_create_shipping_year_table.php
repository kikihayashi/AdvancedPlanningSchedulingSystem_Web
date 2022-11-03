<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingYearTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_year', function (Blueprint $table) {
            $table->id();
            $table->integer('version');
            $table->integer('period');
            $table->string('item_code');
            $table->string('item_name');
            $table->integer('lot_no');
            $table->integer('lot_total');
            $table->string('transport_id');
            $table->string('remark')->nullable();
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
            $table->unique(['version', 'period', 'item_code', 'lot_no', 'transport_id']); //讓組合不會重複
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_year');
    }
}
