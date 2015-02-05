<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCalculatedTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('calculated', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('in');
			$table->integer('out');
			$table->text('error');
			$table->text('owner'); // кто обработал поле
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('calculated');
	}

}
