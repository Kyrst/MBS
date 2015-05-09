<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSongsTable extends Migration
{
	public function up()
	{
		Schema::create('user_songs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');
			$table->string('title', 200);
			$table->string('slug', 200);
			$table->enum('status', array_keys(\App\Models\User_Song::getStatuses()))->default(\App\Models\User_Song::STATUS_PROCESSING);
			$table->timestamps();
		});

		Schema::create('user_song_versions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_song_id')->unsigned();
			$table->foreign('user_song_id')->references('id')->on('user_songs');
			$table->smallInteger('version')->unsigned();
			$table->string('original_filename', 200);
			$table->integer('file_size')->unsigned();
			$table->string('extension', 10);
			$table->string('mime_type', 50);
			$table->float('duration', 6, 2)->unsigned()->nullable();
			$table->float('bpm', 6, 2)->unsigned()->nullable();
			$table->enum('key', ['a', 'am', 'bb', 'bbm', 'b', 'bm', 'c', 'cm', 'db', 'dbm', 'd', 'dm', 'eb', 'ebm', 'e', 'em', 'f', 'fm', 'gb', 'gbm', 'g', 'gm', 'ab', 'abm'])->nullable();
			$table->binary('waveform_data')->nullable();
			$table->enum('status', array_keys(\App\Models\User_Song::getStatuses()))->default(\App\Models\User_Song::STATUS_PROCESSING);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('user_song_versions');
		Schema::drop('user_songs');
	}
}