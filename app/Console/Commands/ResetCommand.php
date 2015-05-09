<?php namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetCommand extends Command
{
	protected $name = 'user:reset';

	protected $description = 'Reset data';

	public function fire()
	{
		$this->info('Delete database tables...');

		$result_tables = \DB::select('SHOW TABLES');

		$tables = array();

		foreach ( $result_tables as $table )
		{
			$tables[] = $table->Tables_in_mybetasongs;
		}

		if ( count($tables) > 0 )
		{
			\DB::statement('SET foreign_key_checks = 0');
			\DB::statement('DROP TABLE IF EXISTS ' . implode(', ', $tables) . ';');
			\DB::statement('SET foreign_key_checks = 1');
		}

		$this->info('Run migration...');

		exec('sudo php artisan migrate');
		exec('sudo php artisan db:seed');

		exec('sudo rm -rf uploads/*');

		$this->info('Reset complete.');
	}
}