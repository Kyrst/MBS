<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User_Song;

class CheckSpotifyTracksCommand extends Command
{
	protected $name = 'user:check_spotify_tracks';

	protected $description = 'Check Spotify tracks';

	public function fire()
	{
		$user_songs = User_Song::all();

		foreach ( $user_songs as $user_song )
		{
			$this->info('~ ' . $user_song->title . ' ~');

			$user_song->updateTracksOnSpotify();
		}
	}
}