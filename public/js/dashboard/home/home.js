var $songs_container = $('#songs_container'),
	songs_container_loading_html = $songs_container.html(),
	songs_container_dropzones = [];

var song_waveforms = [],
	song_progress_waveforms = [];

var dropzone = new Dropzone
(
	'body',
	{
		paramName: 'file',
		maxFilesize: 30, // MB
		clickable: false,
		createImageThumbnails: false,
		acceptedFiles: '.mp3,.wav',
		url: $core.uri.urlize('dashboard/upload-song'),
		headers: { 'X-XSRF-TOKEN': $core.options.csrf_token }
	}
);

dropzone.on('addedfile', function(file)
{
	console.log('added ' + file.name);
});

dropzone.on('sending', function(file, xhr, formData)
{
	console.log('sending ' + file.name);

	//formData.append('user_song_id', 1);
});

dropzone.on('uploadprogress', function(file, percent, bytesSent)
{
	console.log(percent + '%');
});

dropzone.on('processing', function(file)
{
	console.log('processing ' + file.name);
});

dropzone.on('success', function(files, response)
{
	if ( response.error !== null )
	{
		$('.dz-preview').find('.dz-progress').fadeOut(function()
		{
			$(this).html(response.error);
			$(this).css('background-color', $('.dz-preview').css('background-color'));

			$(this).fadeIn(function()
			{
			});
		});
	}
	else
	{
		refresh_songs();

		$('.dz-preview').find('.dz-progress').fadeOut('fast', function()
		{
			$(this).html(response.html);
			$(this).css('background-color', $('.dz-preview').css('background-color'));

			$(this).fadeIn('fast', function()
			{
			});
		});
	}
});

dropzone.on('success', function()
{
	console.log('success');
});

dropzone.on('complete', function(file, response) // Called when the upload was either successful or erroneous
{
	console.log(file.name + ' completed!');
});

dropzone.on('canceled', function()
{
	console.log('canceled');
});

dropzone.on('error', function(e, errorMessage)
{
	$core.ui.message.error(errorMessage);
});

function refresh_songs()
{
	$songs_container.css(
	{
		'height': $songs_container.height()
	});

	$songs_container.html(songs_container_loading_html).removeClass('no-data').addClass('is-loading');

	$core.ajax.get
	(
		$core.uri.urlize('dashboard/get-songs'),
		{
		},
		{
			success: function(result)
			{
				if ( result.data.num_songs === 0 )
				{
					$songs_container.html(result.data.html).addClass('no-data');

					songs_container_binds(result);
				}
				else
				{
					$songs_container.html(songs_container_loading_html + result.data.html);

					audio_player_manager.refresh(true);

					var $livestamps = $songs_container.find('time[data-livestamp]'),
						num_livestamps = $livestamps.length;

					$livestamps.each(function(livestamp_index, livestamp_element)
					{
						$(livestamp_element).on('change.livestamp', function(event, from, to)
						{
							var init = (from === '');

							if ( init === true && livestamp_index === (num_livestamps - 1) )
							{
								$('#songs_container_loader').remove();
								$songs_container.removeClass('is-loading');

								songs_container_binds(result);
							}
						});
					});
				}
			},
			error: function()
			{
			}
		}
	);
}

refresh_songs();

function songs_container_binds(result)
{
	update_semantic_ui();

	$songs_container.css(
	{
		'height': 'auto'
	});

	if ( result.data.num_songs > 0 )
	{
		for ( var i = 0; i < result.data.num_songs; i++ )
		{
			var song = result.data.songs[i];

			// Dropzone
			songs_container_dropzones[song.id] = new Dropzone
			(
				'#song_' + song.id,
				{
					paramName: 'file',
					maxFilesize: 30, // MB
					clickable: false,
					createImageThumbnails: false,
					acceptedFiles: '.mp3,.wav',
					url: $core.uri.urlize('dashboard/upload-song'),
					headers: { 'X-XSRF-TOKEN': $core.options.csrf_token }
				}
			);

			songs_container_dropzones[song.id].on('addedfile', function(file)
			{
				console.log('added ' + file.name);
			});

			songs_container_dropzones[song.id].on('sending', function(file, xhr, formData)
			{
				console.log('sending ' + file.name);

				formData.append('user_song_id', song.id);
			});

			$songs_container.find('.version-dropdown').dropdown();

			// Waveform
			if ( song.waveform_data === null )
			{
				continue;
			}

			var waveform_progress_element = document.getElementById('song_waveform_progress_' + song.id);

			song_progress_waveforms[song.id] = new Waveform(
			{
				container: waveform_progress_element,
				outerColor: 'transparent',
				innerColor: 'rgba(0, 0, 0, .2)'
			});

			song_progress_waveforms[song.id].update(
			{
				data: song.waveform_data.left
			});

			var waveform_element = document.getElementById('song_waveform_' + song.id);

			song_waveforms[song.id] = new Waveform(
			{
				container: waveform_element,
				outerColor: '#FFF'
			});

			var context = song_waveforms[song.id].context;

			var gradient = context.createLinearGradient(0, 0, 0, song_waveforms[song.id].height);
			gradient.addColorStop(0.0, '#665AA4');
			gradient.addColorStop(1.0, '#4A4376');
			song_waveforms[song.id].innerColor = gradient;

			song_waveforms[song.id].update(
			{
				data: song.waveform_data.left
			});

			waveform_element.className = 'song-waveform loaded';
		}

		$songs_container.find('.delete-song-button').on('click', function()
		{
			var $delete_button = $(this),
				$song = $delete_button.closest('.song'),
				song_id = $song.data('id'),
				song_title = $song.data('title');

			$core.ui.message.setEngine(new Core_UI_Message_Engine_SweetAlert());
			$core.ui.message.confirm('Are you sure you want to delete song "' + song_title + '"?', function()
			{
				$delete_button.prop('disabled', true).text('Deleting...');

				$core.ajax.post
				(
					$core.uri.urlize('delete-song'),
					{
						id: song_id
					},
					{
						success: function(result)
						{
							//$song.fadeOut('fast', function()
							//{
								$core.ui.message.success('Song "' + song_title + '" was deleted.');

								refresh_songs();
							//});
						},
						error: function()
						{
							$delete_button.text('Delete').prop('disabled', false);

							$core.ui.message.error('Could not delete song right now.');
						}
					}
				);
			});
			$core.ui.message.setEngine(new Core_UI_Message_Engine_Noty());
		});
	}
}