<?php namespace App\Models;

use Illuminate\Support\Facades\DB;

class User_Song extends \Illuminate\Database\Eloquent\Model
{
	const STATUS_PROCESSING = 'processing';
	const STATUS_PROCESSED = 'processed';

	const URL_VIEW = 1;

	public $table = 'user_songs';

	public function versions()
	{
		return $this->hasMany('\App\Models\User_Song_Version', 'user_song_id', 'id');
	}

	public function getAddedUnix()
	{
		return strtotime($this->created_at);
	}

	public static function getStatuses()
	{
		return
		[
			self::STATUS_PROCESSING => 'Processing',
			self::STATUS_PROCESSED => 'Processed'
		];
	}

	public function scopeNewestFirst($query)
	{
		return $query->orderBy('created_at', 'DESC');
	}

	public function getNextVersionNumber()
	{
		$max_version = DB::table('user_song_versions')
			->where('user_song_id', $this->id)
			->max('version');

		return ($max_version !== null ? ($max_version + 1) : 1);
	}

	public function getLatestVersion()
	{
		return $this->versions()
			->orderBy('version', 'DESC')
			->first();
	}

	public static function getUploadDirectory($user_id, $user_song_id, $version = 1, $create_if_not_exist = false)
	{
		$path = base_path('uploads/' . $user_id . '/' . $user_song_id . '/' . $version . '/');

		if ( !file_exists($path) )
		{
			exec('sudo mkdir ' . $path);
		}

		return $path;
	}

	public function getURL($type)
	{
		if ( $type === self::URL_VIEW )
		{
			return \URL::to('dashboard/songs/' . $this->slug);
		}
	}
}