<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * Create teams table
         */
        Schema::create('teams', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('competition_id')->unsigned();
            $table->index('competition_id', 'competition_id');
            $table->string('name');
            $table->decimal('score', 10, 0);
            $table->enum('place', array('honorable', 'third', 'second', 'first'))->nullable();
            $table->boolean('disqualified');
        });

        /**
         * Create foreign key constraint
         */
        Schema::table('teams', function($table) {
            $table->foreign('competition_id')
                ->references('id')
                ->on('competitions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('teams');
    }
}
