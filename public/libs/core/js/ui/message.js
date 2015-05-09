function Core_UI_Message() {}

Core_UI_Message.prototype =
{
	MESSAGE_TYPE_DEFAULT: 1,
	MESSAGE_TYPE_SUCCESS: 2,
	MESSAGE_TYPE_INFO: 3,
	MESSAGE_TYPE_WARNING: 4,
	MESSAGE_TYPE_ERROR: 5,
	MESSAGE_TYPE_CONFIRM: 6,

	engine: null,

	init: function()
	{
		if ( $core.isDefined($core.options.message.engine) )
		{
			this.setEngine($core.options.message.engine);
		}
	},

	afterDomInit: function()
	{
		if ( typeof core_message !== 'undefined' )
		{
			this.engine.show(core_message.type, core_message.text);
		}
	},

	setEngine: function(engine)
	{
		this.engine = engine;
		this.engine.core_ui_message = this;
	},

	success: function(text, options)
	{
		this.engine.show(this.MESSAGE_TYPE_SUCCESS, text, options);
	},

	info: function(text, options)
	{
		this.engine.show(this.MESSAGE_TYPE_INFO, text, options);
	},

	warning: function(text, options)
	{
		this.engine.show(this.MESSAGE_TYPE_WARNING, text, options);
	},

	error: function(text, options)
	{
		this.engine.show(this.MESSAGE_TYPE_ERROR, text, options);
	},

	confirm: function(text, confirm_callback, cancel_callback, options)
	{
		if ( $core.isUndefined(options) )
		{
			options = {};
		}

		options.confirm_callback = confirm_callback;
		options.cancel_callback = cancel_callback;

		this.engine.show(this.MESSAGE_TYPE_CONFIRM, text, options);
	}
};