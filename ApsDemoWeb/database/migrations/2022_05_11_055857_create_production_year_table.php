<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionYearTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_year', function (Blueprint $table) {
            $table->id();
            $table->integer('version');
            $table->integer('period');
            $table->string('item_code');
            $table->string('item_name');
            $table->integer('lot_no');
            $table->integer('lot_total');
            $table->string('remark')->nullable();
            $table->string('deadline')->nullable();
            $table->string('order_no');
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
            $table->string('range_4')->nullable();
            $table->string('range_5')->nullable(); 
            $table->string('range_6')->nullable(); 
            $table->string('range_7')->nullable(); 
            $table->string('range_8')->nullable(); 
            $table->string('range_9')->nullable(); 
            $table->string('range_10')->nullable(); 
            $table->string('range_11')->nullable(); 
            $table->string('range_12')->nullable();
            $table->string('range_1')->nullable();
            $table->string('range_2')->nullable();
            $table->string('range_3')->nullable();
            $table->string('remark_hidden_4')->nullable();
            $table->string('remark_hidden_5')->nullable();
            $table->string('remark_hidden_6')->nullable();
            $table->string('remark_hidden_7')->nullable();
            $table->string('remark_hidden_8')->nullable();
            $table->string('remark_hidden_9')->nullable();
            $table->string('remark_hidden_10')->nullable();
            $table->string('remark_hidden_11')->nullable();
            $table->string('remark_hidden_12')->nullable();
            $table->string('remark_hidden_1')->nullable();
            $table->string('remark_hidden_2')->nullable();
            $table->string('remark_hidden_3')->nullable();
            $table->timestamps();
            $table->unique(['version', 'period', 'item_code', 'lot_no']); //讓組合不會重複
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_year');
    }
}
