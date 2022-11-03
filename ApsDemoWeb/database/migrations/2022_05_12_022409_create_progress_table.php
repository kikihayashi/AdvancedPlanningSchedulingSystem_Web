<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('progress', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->integer('version');
            $table->integer('period');
            $table->integer('month');
            $table->integer('progress_point');
            $table->string('create_project');
            $table->timestamps();
            $table->unique(['project_name', 'period', 'month']); //讓這些組合不會重複
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('progress');
    }
}
