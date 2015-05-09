<?php namespace App\Models;

class User_Song_Version extends \Illuminate\Database\Eloquent\Model
{
	const STATUS_PROCESSING = 'processing';
	const STATUS_PROCESSED = 'processed';

	public $table = 'user_song_versions';

	public function userSong()
	{
		return $this->belongsTo('\App\Models\User_Song', 'user_song_id', 'id');
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

	public function getBPM($fallback = null)
	{
		if ( $this->bpm !== null )
		{
			$bpm = number_format($this->bpm, 1);

			if ( substr($bpm, -2, 2) === '.0' )
			{
				$bpm = substr($bpm, 0, -2);
			}
		}
		else
		{
			$bpm = $fallback;
		}

		return $bpm;
	}

	public function getKey($fallback = null)
	{
		if ( $this->key !== null )
		{
			switch ( $this->key )
			{
				case 'a': return 'A';
				case 'am': return 'A Major';
				case 'ab': return 'A♭';
				case 'abm': return 'G# Major';
				case 'b': return 'B';
				case 'bm': return 'B Major';
				case 'bb': return 'B♭';
				case 'bbm': return 'A# Major';
				case 'c': return 'C';
				case 'cm': return 'C Major';
				case 'd': return 'D';
				case 'dm': return 'D Major';
				case 'db': return 'D♭';
				case 'dbm': return 'C# Major';
				case 'f': return 'F';
				case 'fm': return 'F Major';
				case 'g': return 'G';
				case 'gm': return 'G Major';
				case 'gb': return 'G♭';
				case 'gbm': return 'F# Major';
				default: return $this->key;
			}
		}
		else
		{
			return $fallback;
		}
	}

	public static function getKeys()
	{
		return
		[
			'a' => 'A',
			'am' => 'A Major',
			'ab' => 'A♭',
			'abm' => 'G# Major',
			'b' => 'B',
			'bm' => 'B Major',
			'bb' => 'B♭',
			'bbm' => 'A# Major',
			'c' => 'C',
			'cm' => 'C Major',
			'd' => 'D',
			'dm' => 'D Major',
			'db' => 'D♭',
			'dbm' => 'C# Major',
			'f' => 'F',
			'fm' => 'F Major',
			'g' => 'G',
			'gm' => 'G Major',
			'gb' => 'G♭',
			'gbm' => 'F# Major'
		];
	}

	public function scopeNewestFirst($query)
	{
		return $query->orderBy('created_at', 'DESC');
	}

	public function getFileURL()
	{
		return \URL::to('listen/' . $this->user_song_id . '/' . $this->version . '/' . $this->original_filename);
	}
}