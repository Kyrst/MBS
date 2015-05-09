function Audio_Player_Manager() {}

Audio_Player_Manager.prototype =
{
	options:
	{
		selector: '.song',
		refreshOnInit: false
	},

	songs: [],
	num_songs: 0,
	current_song_id: null,

	init: function(options)
	{
		var self = this;

		if ( typeof options === 'object' )
		{
			self.options = $.extend(self.options, options);
		}

		if ( self.options.refreshOnInit === true )
		{
			self.refresh(true);
		}

		$('#header_volume').on('input change', function(e)
		{
			var volume = e.target.value;

			self.setVolume(volume);

			if ( e.type === 'change' )
			{
				$core.ajax.post
				(
					$core.uri.urlize('save-volume'),
					{
						volume: volume
					}
				);
			}
		});
	},

	refresh: function(from_init)
	{
		var self = this,
			songs = document.querySelectorAll('.song');

		if ( typeof from_init === 'undefined' )
		{
			from_init = false;
		}

		self.num_songs = songs.length;

		for ( var song_index = 0; song_index < self.num_songs; song_index++ )
		{
			var song = songs[song_index];

			var audio_player_manager_song = new Audio_Player_Manager_Song();
			audio_player_manager_song.init(song);

			self.songs[song.id] = audio_player_manager_song;
		}

		if ( from_init === true )
		{
			self.setVolume(volume);

			var player = Locstor.get('player');

			if ( player !== null )
			{

			}
		}
	},

	getSongs: function()
	{
		return this.songs;
	},

	save: function()
	{
		var self = this;

		var key = 'song_' + self.current_song_id;

		var current_audio_player_manager_song = self.songs[key],
			player = (typeof current_audio_player_manager_song !== 'undefined' ? current_audio_player_manager_song.player : null);

		Locstor.set('player',
		{
			current_song_id: self.current_song_id,
			is_playing: (player ? !player.isPaused() : false),
			current_time: (player ? player.getTime() : 0)
		});
	},

	setVolume: function(new_volume)
	{
		var self = this;

		for ( var song_id in self.songs )
		{
			var song = self.songs[song_id];

			song.player.setVolume(new_volume);
		}
	}
};