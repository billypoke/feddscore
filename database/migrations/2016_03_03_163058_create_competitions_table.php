<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $grammar = DB::connection()->withTablePrefix(new ExtendedMySqlGrammar());
        // set new grammar class
        DB::connection()->setSchemaGrammar($grammar);

        // get custom schema object
        $schema = DB::connection()->getSchemaBuilder();

        // bind new blueprint class
        $schema->blueprintResolver(function ($table, $callback) {
            return new ExtendedBlueprint($table, $callback);
        });

        // then create tables
        $schema->create('competitions', function (ExtendedBlueprint $table) {
        $table->engine = 'InnoDB';
        $table->increments('id');
        $table->year('year');
        $table->enum('ampm', array('am', 'pm'));
        $table->string('name');
        $table->enum('status', array('waiting', 'active', 'final'));
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('competitions');
    }
}

class ExtendedBlueprint extends Blueprint
{
    /**
     * create a new year column
     *
     * @param $column
     * @return \Illuminate\Support\Fluent
     */
    public function year($column)
    {
        return $this->addColumn('year', $column);
    }
}

class ExtendedMySqlGrammar extends Illuminate\Database\Schema\Grammars\MySqlGrammar
{
    /**
     * Create the column definition for a year type.
     *
     * @return string
     */
    protected function typeYear()
    {
        return "year(4)";
    }
}
