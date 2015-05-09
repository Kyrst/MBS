var $sign_in_form = $('#sign_in_form');

var $email = $('#email');
var $password = $('#password');

var $sign_in_button = $('#sign_in_button');
$sign_in_button.attr('data-default_text', $sign_in_button.text());

$sign_in_form.on('submit', function()
{
	var email = $email.val();
	var password = $password.val();
	var error = false;

	if ( password === '' )
	{
		$('#password_field').addClass('error');

		$password.focus();

		error = true;
	}
	else
	{
		$('#password_field').removeClass('error');
	}

	if ( email === '' )
	{
		$('#email_field').addClass('error');

		$email.focus();

		error = true;
	}
	else
	{
		$('#email_field').removeClass('error');
	}

	if ( error )
	{
		return false;
	}

	$sign_in_form.addClass('loading');
	$sign_in_button.addClass('disabled').text('Signing In...');

	var reset_form = function()
	{
		$sign_in_button.text($sign_in_button.data('default_text')).removeClass('disabled');
		$sign_in_form.removeClass('loading');
	};

	$core.ajax.post
	(
		$sign_in_form.attr('action'),
		{
			email: $email.val(),
			password: $password.val()
		},
		{
			success: function(result)
			{
				if ( result.error === null )
				{
					$core.uri.redirect('dashboard', true);
				}
				else
				{
					$core.ui.message.error(result.error);

					reset_form();
				}
			},
			error: function()
			{
				$core.ui.message.error('Could not sign in right now.');

				reset_form();
			}
		}
	);

	return false;
});