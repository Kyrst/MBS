<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Str;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
	const PROFILE_PAGE = 1;
	const EDIT_URL = 2;
	const DELETE_URL = 3;

	use Authenticatable, CanResetPassword, \App\Helpers\Core\DynamicModel;

	protected $table = 'users';

	protected $fillable = ['name', 'email', 'password'];

	protected $hidden = ['password', 'remember_token'];

	public function songs()
	{
		return $this->hasMany('\App\Models\User_Song', 'user_id', 'id');
	}

	public static function getDynamicItemColumns()
	{
		return
		[
			'email' =>
			[
				'title' => 'Email',
				'column' => 'email',
				'form' =>
				[
					'type' => 'text',
					'name' => 'email',
					'validation' =>
					[
						'required' => ALWAYS_REQUIRED
					]
				]
			],
			'first_name' =>
			[
				'title' => 'First Name',
				'column' => 'first_name',
				'form' =>
				[
					'type' => 'text',
					'name' => 'first_name',
					'validation' =>
					[
						'required' => ALWAYS_REQUIRED
					]
				]
			],
			'last_name' =>
			[
				'title' => 'Last Name',
				'column' => 'last_name',
				'form' =>
				[
					'type' => 'text',
					'name' => 'last_name',
					'validation' =>
					[
						'required' => ALWAYS_REQUIRED
					]
				]
			]
		];
	}

	public static function initDynamicModel()
	{
		return
		[
			'slug' => NULL,
			'active_toggle' => NULL
		];
	}

	public static function register($email, $alias, $password, $first_name, $last_name)
	{
		$user = User::create(
		[
			'email' => $email,
			'password' => bcrypt($password),
			'alias' => $alias,
			'first_name' => ($first_name),
			'last_name' => trim($last_name)
		]);
	}

	public function getName()
	{
		return $this->first_name . ' ' . $this->last_name;
	}

	public function getURL($page)
	{
		if ( $page === self::PROFILE_PAGE )
		{
			return \URL::to('dashboard/users/user/' . $this->id);
		}
		elseif ( $page === self::EDIT_URL )
		{
			return \URL::to('dashboard/users/user/' . $this->id . '/edit');
		}
	}

	public function getLastAction()
	{
		return $this->actions()->orderBy('created_at', 'DESC')->first();
	}

	public function getAlias()
	{
		return $this->alias;
	}

	public function getUploadDir($user_song_id = null, $create_if_not_exist = false)
	{
		$path = base_path('uploads/' . $this->id . '/' . ($user_song_id !== null ? $user_song_id . '/' : ''));

		if ( $create_if_not_exist === true && !file_exists($path) )
		{
			exec('sudo mkdir ' . $path);
		}

		return $path;
	}

	public function getVolume()
	{
		return $this->volume;
	}
}