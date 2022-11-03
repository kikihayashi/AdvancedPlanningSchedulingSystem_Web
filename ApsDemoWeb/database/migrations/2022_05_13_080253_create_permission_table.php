<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('remark')->nullable();
            $table->string('worker_operation');
            $table->string('supervisor_operation');
            $table->string('manager_operation');
            $table->string('director_operation');
            $table->string('project_crud');
            $table->string('identity_crud');
            $table->string('basic_crud');
            $table->string('maintain_crud');
            $table->string('period_delete');
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
        Schema::dropIfExists('permission');
    }
}
