<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('files', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name',100);
            $table->string('url')->unique();
            $table->bigInteger('size');
            $table->integer('folder_id')->unsigned()->nullable();
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
        });
    }
    public function down()
    {
         Schema::drop('files');
            
    }


}
