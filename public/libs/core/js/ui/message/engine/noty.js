function Core_UI_Message_Engine_Noty() {}

Core_UI_Message_Engine_Noty.prototype =
{
	core_ui_message: null,

	show: function(type, text, options)
	{
		var message_type = null;
		var show_cancel_button = false;

		if ( type === this.core_ui_message.MESSAGE_TYPE_SUCCESS )
		{
			message_type = 'alert';
		}
		else if ( type === this.core_ui_message.MESSAGE_TYPE_INFO )
		{
			message_type = 'alert';
		}
		else if ( type === this.core_ui_message.MESSAGE_TYPE_WARNING )
		{
			message_type = 'warning';
		}
		else if ( type === this.core_ui_message.MESSAGE_TYPE_ERROR )
		{
			message_type = 'error';
		}
		else if ( type === this.core_ui_message.MESSAGE_TYPE_CONFIRM )
		{
			show_cancel_button = true;
		}

		noty(
		{
			layout: 'topRight',
			type: message_type,
			text: text,
			timeout: 2000,
			maxVisible: 1
		});

		/*swal(
		{
			title: '',
			text: text,
			type: message_type,
			showCancelButton: show_cancel_button
		}, function(confirmed)
		{
			if ( $core.isDefined(options) )
			{
				if ( confirmed === true && $core.isFunction(options.confirm_callback) )
				{
					options.confirm_callback()
				}
				else if ( confirmed === false && $core.isFunction(options.cancel_callback) )
				{
					options.cancel_callback();
				}
			}
		});*/
	}
};