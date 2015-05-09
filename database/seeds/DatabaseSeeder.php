<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class DatabaseSeeder extends Seeder
{
	public function run()
	{
		Model::unguard();

		$this->call('UserTableSeeder');
	}
}

class UserTableSeeder extends Seeder
{
	public function run()
	{
		User::register('dennis@mybetasongs.com', 'Kyrst', 'dennis', 'Dennis', 'Nygren');
	}
}