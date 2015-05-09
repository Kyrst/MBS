function Audio_Player_Manager_Song() {}

Audio_Player_Manager_Song.prototype =
{
	audio_player_manager: null,

	options:
	{
	},

	song_element: null,
	song_id: null,
	version: null,
	url: null,

	player: null,

	offset: function(element)
	{
		var rect = element.getBoundingClientRect(),
			body_element = document.body;

		return {
			top: rect.top + body_element.scrollTop,
			left: rect.left + body_element.scrollLeft
		};
	},

	init: function(song_element, options)
	{
		var self = this,
			_song_id = song_element.getAttribute('data-id'),
			title = song_element.getAttribute('data-title');

		self.song_element = song_element;
		self.song_id = _song_id;
		self.version = song_element.getAttribute('data-version');
		self.url = song_element.getAttribute('data-url');

		var previous_song_id = audio_player_manager.current_song_id;

		if ( typeof options === 'object' )
		{
			self.options = $.extend(self.options, options);
		}

		if ( self.version !== null && (self.url !== null && self.url !== '') )
		{
			self.player = new buzz.sound(self.url);

			self.player.bind('error', function(e)
			{
				alert(this.getErrorMessage());
			});

			self.player.bind('loadstart', function(e)
			{
				//console.log('loadstart');
			}).bind('loadeddata', function(e)
			{
				// Done loading
				//console.log('loadeddata');
			}).bind('error', function(e)
			{
				//console.log('error');
			}).bind('progress', function(e)
			{
				//console.log('progress');
			}).bind('canplay canplaythrough', function(e)
			{
				//console.log('canplay canplaythrough');
			}).bind('timeupdate', function()
			{
				var time = this.getTime(),
					duration = this.getDuration();

				if ( duration === '--' )
				{
					return;
				}

				var percent = buzz.toPercent(time, duration);
				$('#header_player_progress').val(percent);

				$('#header_player_position').html(buzz.toTimer(time));
				$('#header_player_duration').html(buzz.toTimer(duration));

				var parent_waveform_width = parseInt(song_waveforms[self.song_id].canvas.width, 10),
					progress_waveform_width = ((percent / 100) * parent_waveform_width);

				song_progress_waveforms[self.song_id].canvas.style.clip = 'rect(0, ' + progress_waveform_width + 'px, ' + progress_waveform_width + 'px, 0)' + 'px';
				song_progress_waveforms[self.song_id].redraw();
			});

			self.player.load();

			// Waveform click event
			var song_waveform_container_element = document.getElementById('song_waveform_container_' + self.song_id);

			song_waveform_container_element.addEventListener('click', function(e)
			{
				var song_waveform_container_element_offset = self.offset(song_waveform_container_element),
					x = e.pageX - song_waveform_container_element_offset.left,
					width = song_waveform_container_element.clientWidth,
					percent = Math.round((x / width) * 100);

				if ( percent === Infinity )
				{
					return;
				}

				self.player.setPercent(percent);

				if ( self.player.isPaused() )
				{
					self.player.play();
				}

				if ( self.song_id !== previous_song_id )
				{
					audio_player_manager.current_song_id = self.song_id;

					$('#header_player_title').html(title);

					// Pause the other song if it's playing
					if ( previous_song_id !== null )
					{
						if ( !audio_player_manager.songs[previous_song_id].isPaused() )
						{
							audio_player_manager.songs[previous_song_id].pause();
						}
					}

					if ( previous_song_id === null )
					{
						$('#header_player_container').addClass('show');

						$('#header_player_progress').off('click').on('click', function(e)
						{
							var element_offset = $(this).offset(),
								x = e.pageX - element_offset.left,
								width = $(this).width(),
								percent = Math.round((x / width) * 100);

							if ( percent === Infinity )
							{
								return;
							}

							self.player.setPercent(percent);

							/*if ( self.player.isPaused() )
							{
								self.player.play();
							}*/
						});

						$('#header_player_play_button').off('click').on('click', function()
						{
							if ( self.player.isPaused() )
							{
								self.player.play();

								$('#header_player_play_button_icon').removeClass('play').addClass('pause');
							}
							else
							{
								self.player.pause();

								$('#header_player_play_button_icon').removeClass('pause').addClass('play');
							}
						});

						$('#header_player_stop_button').off('click').on('click', function()
						{
							self.player.stop();

							self.player = null;

							$('#header_player_container').removeClass('show');

							$('#header_player_play_button_icon').removeClass('pause').addClass('play');
						});
					}
				}

				var new_width = (e.pageX - song_waveform_container_element_offset.left) + 'px';

  				song_progress_waveforms[self.song_id].canvas.style.clip = 'rect(0, ' + new_width + 'px, ' + new_width + 'px, 0)';
				song_progress_waveforms[self.song_id].redraw();
			});
		}
	}
};