<?php namespace App\Http\Controllers\Dashboard;

use App\Models\User_Song;
use App\Models\User_Song_Version;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UploadController extends \App\Http\Controllers\DashboardController
{
	public function uploadSong()
	{
		$respond = function($result, $error = null)
		{
			if ( $error !== null )
			{
				$result['error'] = $error;
			}

			return \Response::json($result);
		};

		$result =
		[
			'html' => '',
			'error' => null
		];

		if ( $this->user === null )
		{
			return $respond($result, 'Not logged in.');
		}

		$file = \Input::file('file');

		$user_song_id = \Input::get('user_song_id');

		if ( $user_song_id !== null )
		{
			$user_song = User_Song::where('id', $user_song_id)->first();

			if ( $user_song === null )
			{
				return $respond($result, 'Could not find user song with ID "' . $user_song_id . '".');
			}
		}
		else
		{
			$user_song = null;
		}

		$latest_user_user_song = User_Song::select(['id'])->orderBy('id', 'DESC')->limit(1)->first();
		$latest_user_song_id = ($latest_user_user_song !== null ? $latest_user_user_song->id : 0);
		$next_user_song_id = $latest_user_song_id + 1;
		$version = ($user_song !== null ? $user_song->getLatestVersion()->version + 1 : 1);

		$upload_dir = User_Song::getUploadDirectory($this->user->id, $next_user_song_id, $version, true);//$this->user->getUploadDir($next_user_song_id, true);
		$original_filename = $file->getClientOriginalName();
		$extension = $file->getClientOriginalExtension();
		$slug = \App\Helpers\Core\Str::slug(basename($original_filename, '.' . $extension));
		$basename = $next_user_song_id . 'v' . $version;
		$filename = $basename . '.' . $extension;
		$mime_type = $file->getMimeType();
		$file_size = $file->getClientSize();

		try
		{
			$file->move($upload_dir, $filename);
		}
		catch ( FileException $e )
		{
			return $respond($result, $e->getMessage());
		}

		// Convert mp3 to wav
		if ( $extension === 'mp3' )
		{
			$wav_file_path = $upload_dir . $basename . '.wav';
			$mp3_file_path = $upload_dir . $filename;

			exec('/usr/local/bin/ffmpeg -i ' . $mp3_file_path . ' -acodec pcm_u8 -ar 22050 ' . $wav_file_path  . ' 2>&1', $mp3_to_wav_output);
			//exec('/usr/local/bin/sox ' . $mp3_file_path . ' -c 2 -t wav ' . $wav_file_path  . ' 2>&1', $mp3_to_wav_output);

			/*if ( count($mp3_to_wav_output) > 0 )
			{
				print('Error converting MP3 to WAV.');
				die('<pre>' . print_r($mp3_to_wav_output, TRUE) . '</pre>');
			}*/
		}
		elseif ( $extension === 'wav' ) // Convert wav to mp3
		{
			$mp3_file_path = $upload_dir . $basename . '.mp3';
			$wav_file_path = $upload_dir . $filename;

			exec('/usr/local/bin/ffmpeg -i ' . $wav_file_path . ' -acodec libmp3lame ' . $mp3_file_path  . ' 2>&1', $wav_to_mp3_output);

			//die('<pre>' . print_r($wav_to_mp3_output, TRUE) . '</pre>');
		}

		// Generate waveform image
		$waveform_json_file_path = $upload_dir . time() . '.json';

		exec('/usr/local/bin/wav2json ' . $wav_file_path . ' --channels left right -n -o ' . $waveform_json_file_path . ' 2>&1', $waveform_output);

		$waveform_data = file_get_contents($waveform_json_file_path);
		$waveform_data_decoded = json_decode($waveform_data, true);

		unlink($waveform_json_file_path);

		// Get duration
		exec('/usr/local/bin/sox ' . $mp3_file_path . ' -n stat 2>&1', $duration_ouput);

		$duration = null;

		if ( isset($duration_ouput[1]) && preg_match('/([0-9\.]+)/', $duration_ouput[1], $duration_matches) )
		{
			$duration = $duration_matches[1];
		}
		else
		{
			ob_start(); var_dump($duration_ouput); $out = ob_get_contents(); ob_end_clean(); error_log($out);
		}

		// Detect tempo
		exec('/usr/local/bin/soundstretch ' . $wav_file_path . ' -bpm 2>&1', $soundstretch_output);

		$bpm = null;

		if ( isset($soundstretch_output[9]) && preg_match('/([0-9\.]+)/', $soundstretch_output[9], $detect_tempo_matches) )
		{
			$bpm = $detect_tempo_matches[1];
		}
		else
		{
			ob_start(); var_dump($soundstretch_output); $out = ob_get_contents(); ob_end_clean(); error_log($out);
		}

		// Detect key
		exec(base_path('libs/KeyFinder/KeyFinder.app/Contents/MacOS/KeyFinder') . ' -f ' . $mp3_file_path . ' 2>&1', $keyfinder_output);

		$key = null;

		if ( isset($keyfinder_output[4]) )
		{
			$key = strtolower($keyfinder_output[4]);
		}
		else
		{
			ob_start(); var_dump($keyfinder_output); $out = ob_get_contents(); ob_end_clean(); error_log($out);
		}

		// Meta
		/*try
		{
			$audio = new \MpegAudioDataExtractor($mp3_file_path, \MpegAudioDataExtractor::META | \MpegAudioDataExtractor::TAGS);
		}
		catch ( \MpegAudioParserException $e )
		{
		}*/

		$title = $original_filename;

		if ( $user_song === null )
		{
			$user_song = new User_Song();
			$user_song->user_id = $this->user->id;
			$user_song->title = $title;
			$user_song->slug = $slug;
		}

		$user_song->status = \App\Models\User_Song::STATUS_PROCESSING;
		$user_song->save();

		$user_song_version = new User_Song_Version();
		$user_song_version->user_song_id = $user_song->id;
		$user_song_version->version = $version;
		$user_song_version->original_filename = $original_filename;
		$user_song_version->file_size = $file_size;
		$user_song_version->extension = $extension;
		$user_song_version->mime_type = $mime_type;
		$user_song_version->duration = $duration;
		$user_song_version->bpm = $bpm;
		$user_song_version->key = $key;
		$user_song_version->waveform_data = ($waveform_data_decoded !== false ? $waveform_data : null);
		$user_song_version->status = \App\Models\User_Song::STATUS_PROCESSING;
		$user_song_version->save();

		$response_view = view('dashboard/partials/upload_response');
		$response_view->title = $title;

		$result['html'] = $response_view->render();

		return $respond($result);
	}
}