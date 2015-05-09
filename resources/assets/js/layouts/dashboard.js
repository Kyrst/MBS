var dropdown_settings =
{
	transition: 'drop',
	duration: 125
};

$core = new Core();

$core.init(
{
	csrf_token: encrypted_csrf_token,
	uri:
	{
		base_url: base_url
	},
	message:
	{
		engine: new Core_UI_Message_Engine_Noty()
	},
	dynamic_table:
	{
		manage_dropdown_settings: dropdown_settings
	}
});

$(function()
{
	$core.afterDomInit();
});

moment.locale('en',
{
    relativeTime:
    {
        future: '%s',
        past: '%s',
        s: '%d seconds ago',
        m: '%d minute ago',
        mm: '%d minutes ago',
        h: '%d hour ago',
        hh: '%d hours ago',
        d: '%d day ago',
		dd: function (number)
		{
			var weeks = Math.round(number / 7);

			if ( number < 7 )
			{
				return number + ' days ago';
			}
			else
			{
				return weeks + ' week' + (weeks !== 1 ? 's' : '') + ' ago';
			}
		},
        M: '%d month ago',
        MM: '%d months ago',
        y: '%d year ago',
        yy: '%y years ago'
    }
});

$('#header_dropdown').dropdown(
{
	transition: 'drop',
	duration: 125,
	onChange: function()
	{
		$('#header_dropdown').children('.text').html('Updating...');
		//$('#header_dropdown').dropdown('set text("Updating...")');
	}
});

var $forms;

function update_semantic_ui()
{
	$forms = $('form');

	$forms.find('select').dropdown(
	{
		transition: 'slide down',
		duration: 125
	});

	$forms.find('.ui.checkbox').checkbox();
}

update_semantic_ui();

$('.popup').popup();

var $songs_container = $('#songs_container'),
	songs_container_loading_html = $songs_container.html();

$('#update_button').on('click', function()
{
	update_spotify(function()
	{
	});
});

function update_spotify(done_callback)
{
	$songs_container.html(songs_container_loading_html).addClass('is-loading');

	$core.ajax.post
	(
		$core.uri.urlize('update'),
		{
		},
		{
			always: function()
			{
				refresh_songs();

				if ( typeof done_callback === 'function' )
				{
					done_callback();
				}
			}
		}
	);
}

var audio_player_manager = new Audio_Player_Manager();
audio_player_manager.init();

window.onbeforeunload = function()
{
	audio_player_manager.save();
};