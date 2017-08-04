<?php

use Konsulting\Laravel\EditorStamps\Schema;
use Konsulting\Laravel\EditorStamps\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMonitoredCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('command_records', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->json('arguments');
            $table->json('options');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->longText('result');
            $table->editorStamps();
            $table->softDeletes();
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
        Schema::dropIfExists('command_records');
    }
}
