<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStub1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stub1', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->text('surnames')->nullable();
            $table->text('birthday')->nullable();
            $table->enigma();
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
        Schema::dropIfExists('stub1');
    }
}
