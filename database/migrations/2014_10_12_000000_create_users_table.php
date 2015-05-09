<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('email')->unique();
			$table->string('alias', 100);
			$table->string('first_name', 30);
			$table->string('last_name', 30);
			$table->char('password', 60);
			$table->tinyInteger('volume')->unsigned()->default(100);
			$table->rememberToken();
			$table->nullableTimestamps();
		});
	}

	public function down()
	{
		Schema::drop('users');
	}
}