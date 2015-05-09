<?php namespace App\Models;

class Spotify_Track extends \Illuminate\Database\Eloquent\Model
{
	const URL_THIS_IS_IT = 1;

	public $table = 'spotify_tracks';

	public static function deleteWithoutConnection()
	{
		$spotify_tracks_without_connection = Spotify_Track::leftJoin('user_song_spotify_tracks', 'user_song_spotify_tracks.spotify_track_id', '=', 'spotify_tracks.id')
			->whereNull('user_song_spotify_tracks.spotify_track_id')
			->get();

		foreach ( $spotify_tracks_without_connection as $spotify_track )
		{
			$spotify_track->delete();
		}
	}

	public function getURL($type)
	{
		if ( $type === self::URL_THIS_IS_IT )
		{
			return \URL::to('song/' . $this->slug . '/this-is-it');
		}
	}
}