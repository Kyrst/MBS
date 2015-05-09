<?php namespace App\Http\Controllers;

use App\Models\User_Song;
use App\Models\User_Song_Version;

class ListenController extends Controller
{
	public function _listen($user_song_id, $version, $filename)
	{
		$user_song_version = User_Song_Version::where('user_song_id', $user_song_id)
			->where('version', $version)
			->first();

		if ( $user_song_version === null )
		{
			die('Could not find user song version with ID "' . $user_song_id . '" with version ' . $version . '.');
		}

		$upload_dir = User_Song::getUploadDirectory($user_song_version->userSong->user_id, $user_song_id, $version);
		$filename = $user_song_id . 'v' . $version;
		$path = $upload_dir . '/' . $filename . '.' . $user_song_version->extension;

		header('Content-Type: ' . $user_song_version->mime_type);
		header('Content-Length: ' . $user_song_version->file_size);

		readfile($path);
		exit;
	}

	public function listen($user_song_id, $version, $filename)
	{
		$user_song_version = User_Song_Version::where('user_song_id', $user_song_id)
			->where('version', $version)
			->first();

		if ( $user_song_version === null )
		{
			die('Could not find user song version with ID "' . $user_song_id . '" with version ' . $version . '.');
		}

		$upload_dir = User_Song::getUploadDirectory($user_song_version->userSong->user_id, $user_song_id, $version);
		$filename = $user_song_id . 'v' . $version;
		$path = $upload_dir . '/' . $filename . '.' . $user_song_version->extension;

		if ( !file_exists($path) )
		{
			return Response::make()->setStatusCode(404);
		}

		$filesize = filesize($path);

		$range = false;

		if ( isset($_SERVER['HTTP_RANGE']) )
		{
			$range = $_SERVER['HTTP_RANGE'];
		}
		elseif ( $apache = apache_request_headers() )
		{
			$headers = array();

			foreach ( $apache as $header => $value )
			{
				$headers[strtolower($header)] = $value;
			}

			if ( isset($headers['range']) )
			{
				$range = $headers['range'];
			}
		}

		if ( $range )
		{
			$partial = true;

			list($param, $range) = explode('=', $range);

			if ( strtolower(trim($param)) !== 'bytes' )
			{
				return \Response::make()->setStatusCode(400);
			}

			// Get range values
			$range = explode(',', $range);
			$range = explode('-', $range[0]);

			// Deal with range values
			if ( $range[0] === '' )
			{
				$end = $filesize - 1;
				$start = $end - intval($range[0]);
			}
			else if ( $range[1] === '' )
			{
				$start = intval($range[0]);
				$end = $filesize - 1;
			}
			else
			{
				// Both numbers present, return specific range
				$start = intval($range[0]);
				$end = intval($range[1]);

				// Invalid range/whole file specified, return whole file
				if ($end >= $filesize || (!$start && (!$end || $end == ($filesize - 1))))
				{
					$partial = false;
				}
			}

			$length = $end - $start + 1;
		}
		else
		{
			$partial = false;
			$length = $filesize;
		}

		$response = \Response::make();
		$response->header('Content-Type', 'audio/mpeg');
		$response->header('Content-Length', $length);
		$response->header('Accept-Ranges', 'bytes');

		if ( $partial )
		{
			$response->setStatusCode(206);
			$response->header('Content-Range', 'bytes ' . $start . '-' . $end . '/' . $filesize);

			if ( !($fp = fopen($path, 'rb')) )
			{
				$response->setStatusCode(500);

				return $response;
			}

			if ( $start )
			{
				fseek($fp, $start);
			}

			$partial_data = '';

			while ( $length )
			{
				$read = ($length > 8192) ? 8192 : $length;
				$length -= $read;

				$partial_data .= fread($fp, $read);
			}

			fclose($fp);

			$response->setContent($partial_data);
		}
		else
		{
			$response->setContent(file_get_contents($path));
		}

		return $response;
	}
}