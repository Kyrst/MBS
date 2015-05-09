<?php
get('/', [ 'uses' => 'Front\HomeController@home', 'as' => 'home' ]);

get('/listen/{user_song_id}/{version}/{filename}', [ 'uses' => 'ListenController@listen' ]);

post('/sign-in', [ 'uses' => 'Front\AuthController@signInPost', 'as' => 'sign-in' ]);

get('/sign-in', [ 'uses' => 'Front\AuthController@signIn', 'as' => 'sign-in' ]);
get('/sign-out', [ 'uses' => 'Front\AuthController@signOut', 'as' => 'dashboard/sign-out' ]);

get('/dashboard', [ 'uses' => 'Dashboard\HomeController@home', 'as' => 'dashboard' ]);
post('/update', [ 'uses' => 'Dashboard\HomeController@update', 'as' => 'update' ]);
post('/add-song', [ 'uses' => 'Dashboard\HomeController@addSongPost', 'as' => 'add-song' ]);
post('/delete-song', [ 'uses' => 'Dashboard\HomeController@deleteSongPost', 'as' => 'delete-song' ]);
get('/get-songs', [ 'uses' => 'Dashboard\HomeController@getSongs', 'as' => 'get-songs' ]);
post('/song/{song_slug}/this-is-it', [ 'uses' => 'Dashboard\HomeController@thisIsItPost' ])->where('song_slug', '[a-z0-9\-]+');
post('/save-volume', [ 'uses' => 'DashboardController@saveVolume', 'as' => 'save-volume' ]);

post('dashboard/upload-song', [ 'uses' => 'Dashboard\UploadController@uploadSong', 'as' => 'dashboard/upload-song']);
get('dashboard/get-songs', [ 'uses' => 'Dashboard\HomeController@getSongs', 'as' => 'dashboard/upload-song']);

get('/dashboard/users', [ 'uses' => 'Dashboard\UsersController@users', 'as' => 'dashboard/users' ]);
get('/dashboard/users/new', [ 'uses' => 'Dashboard\UsersController@dynamicItem' ]);
post('/dashboard/users/new', [ 'uses' => 'Dashboard\UsersController@saveDynamicItem' ]);
get('/dashboard/users/user/{user_id}', [ 'uses' => 'Dashboard\UsersController@userProfile' ])->where('id', '\d+');
get('/dashboard/users/user/{user_id}/edit', [ 'uses' => 'Dashboard\UsersController@dynamicItem' ])->where('id', '\d+');
post('/dashboard/users/user/{user_id}/edit', [ 'uses' => 'Dashboard\UsersController@saveDynamicItem' ])->where('user_id', '\d+');
get('/dashboard/users/get-dynamic-table', [ 'uses' => 'Dashboard\UsersController@getDynamicTableHTML', 'as' => 'dashboard/users/get-dynamic-table' ]);

if ( Config::get('custom.LOG_DATABASE') === TRUE )
{
	Event::listen('illuminate.query', function ($query, $bindings, $time, $name)
	{
		foreach ( $bindings as $i => $binding )
		{
			if ( $binding instanceof \DateTime )
			{
				$bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
			}
			else if ( is_string($binding) )
			{
				$bindings[$i] = "'$binding'";
			}
		}

		$query = vsprintf(str_replace(array('%', '?'), array('%%', '%s'), $query), $bindings);

		error_log($query . PHP_EOL . '--------------------------------------------------------------');
	});
}