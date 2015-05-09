<?php namespace App\Helpers;

class Spotify
{
	public static function formatTracksResult($tracks_results)
	{
		if ( !isset($tracks_results->tracks, $tracks_results->tracks->items) )
		{
			throw new \Exception('Could not parse Spotify result.');
		}

		$songs = [];

		foreach ( $tracks_results->tracks->items as $track_result )
		{
			$artists = [];

			if ( !isset($track_result->artists) )
			{
				die('<pre>' . print_r($track_result, TRUE) . '</pre>');
			}

			foreach ( $track_result->artists as $artist )
			{
				$artists[] =
				[
					'id' => $artist->id,
					'name' => $artist->name,
					'spotify_link' => $artist->external_urls->spotify,
					'web_link' => $artist->href,
					'uri' => $artist->uri
				];
			}
;
			$songs[] =
			[
				'id' => $track_result->id,
				'name' => $track_result->name,
				'album' =>
				[
					'id' => $track_result->id,
					'name' => $track_result->name,
					'images' => $track_result->album->images,
					'spotify_link' => $track_result->album->external_urls->spotify,
					'web_link' => $track_result->album->href,
					'uri' => $track_result->album->uri,
				],
				'artists' => $artists,
				'duration' => $track_result->duration_ms,
				'isrc' => $track_result->external_ids->isrc,
				'spotify_link' => $track_result->external_urls->spotify,
				'web_link' => $track_result->href,
				'uri' => $track_result->uri,
				'preview_url' => $track_result->preview_url
			];
		}

		return $songs;
	}
}