<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plogs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('level');
            $table->text('message')->nullable();
            $table->text('stack')->nullable();
            $table->string('level_class')->nullable();
            $table->string('level_img')->nullable();
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('plogs');
    }
}
