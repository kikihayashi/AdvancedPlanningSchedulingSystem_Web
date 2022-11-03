<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignatureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signature', function (Blueprint $table) {
            $table->id();
            $table->string('project_name')->unique();
            $table->string('create_department')->nullable();
            $table->string('create_user')->nullable();
            $table->string('review_department')->nullable();
            $table->string('review_user')->nullable();
            $table->string('review_department2')->nullable();
            $table->string('review_user2')->nullable();
            $table->string('admit_department')->nullable();
            $table->string('admit_user')->nullable();        
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
        Schema::dropIfExists('signature');
    }
}
