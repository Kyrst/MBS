function Audio_Player_Manager() {}

Audio_Player_Manager.prototype =
{
	options:
	{
		selector: '.song',
		refreshOnInit: false
	},

	players: [],
	num_players: 0,

	init: function(options)
	{
		var self = this;

		if ( typeof options === 'object' )
		{
			self.options = $.extend(self.options, options);
		}

		if ( self.options.refreshOnInit === true )
		{
			self.refresh();
		}
	},

	refresh: function()
	{
		var songs = document.querySelectorAll('.song'),
			num_songs = songs.length;

		for ( var song_index = 0; song_index < num_songs; song_index++ )
		{
			var song = songs[song_index];

			
		}
	}
};