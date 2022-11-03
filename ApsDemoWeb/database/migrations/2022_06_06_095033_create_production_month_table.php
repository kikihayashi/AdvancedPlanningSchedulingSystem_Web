<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionMonthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_month', function (Blueprint $table) {
            $table->id();
            $table->integer('version');
            $table->integer('period');
            $table->string('item_code');
            $table->string('item_name');
            $table->integer('lot_no');
            $table->integer('month');
            $table->string('previous_month_number');
            $table->string('this_month_number');
            $table->string('start_day_array')->nullable();
            $table->string('end_day_array')->nullable();
            $table->timestamps();
            $table->unique(['version', 'period', 'month', 'item_code', 'lot_no']); //讓組合不會重複
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_month');
    }
}
