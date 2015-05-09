function Core_UI() {}

Core_UI.prototype =
{
	message: null,
	dialog: null,

	init: function()
	{
		this.message = new Core_UI_Message();
		this.message.init();

		this.dialog = new Core_UI_Dialog();
		this.dialog.init();
	},

	afterDomInit: function()
	{
		this.message.afterDomInit();
	},

	setCaretAtEnd: function($element)
	{
		var value_length = $element.value.length;

		if ( document.selection )
		{
			$element.focus();

			var range = document.selection.createRange();
			range.moveStart('character', -value_length);
			range.moveStart('character', value_length);
			range.moveEnd('character', 0);
			range.select();
		}
		else if ( $element.selectionStart || $element.selectionStart === 0 )
		{
			$element.selectionStart = value_length;
			$element.selectionEnd = value_length;
			$element.focus();
		}
	},
	viewport: function()
	{
		var e = window,
			a = 'inner';

		if ( !('innerWidth' in window) )
		{
			a = 'client';
			e = document.documentElement || document.body;
		}

		return { width: e[a + 'Width'], height: e[a + 'Height'] };
	}
};