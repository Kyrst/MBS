function Core_Ajax() {}

Core_Ajax.prototype =
{
	requests: [],

	get: function(url, data, callbacks, extra)
	{
		var request = new Core_Ajax_Request();
		request.method = 'GET';
		request.url = url;
		request.data = data;

		if ( $core.isUndefined(callbacks) )
		{
			callbacks = {};
		}

		if ( $core.isFunction(callbacks.before) )
		{
			request.before = callbacks.before;
		}

		if ( $core.isFunction(callbacks.progress) )
		{
			request.progress = callbacks.progress;
		}

		if ( $core.isFunction(callbacks.success) )
		{
			request.success = callbacks.success;
		}

		if ( $core.isFunction(callbacks.error) )
		{
			request.error = callbacks.error;
		}

		if ( $core.isFunction(callbacks.always) )
		{
			request.always = callbacks.always;
		}

		if ( $core.isFunction(callbacks.abort) )
		{
			request.abort = callbacks.abort;
		}

		if ( $core.isObject(extra) )
		{
			for ( var key in extra )
			{
				if ( extra.hasOwnProperty(key) )
				{
					request[key] = extra[key];
				}
			}
		}

		request.execute();
	},

	post: function(url, data, callbacks, extra)
	{
		var request = new Core_Ajax_Request();
		request.method = 'POST';
		request.url = url;
		request.data = data;

		if ( $core.isUndefined(callbacks) )
		{
			callbacks = {};
		}

		if ( $core.isFunction(callbacks.before) )
		{
			request.before = callbacks.before;
		}

		if ( $core.isFunction(callbacks.progress) )
		{
			request.progress = callbacks.progress;
		}

		if ( $core.isFunction(callbacks.success) )
		{
			request.success = callbacks.success;
		}

		if ( $core.isFunction(callbacks.error) )
		{
			request.error = callbacks.error;
		}

		if ( $core.isFunction(callbacks.always) )
		{
			request.always = callbacks.always;
		}

		if ( $core.isFunction(callbacks.abort) )
		{
			request.abort = callbacks.abort;
		}

		if ( $core.isObject(extra) )
		{
			for ( var key in extra )
			{
				if ( extra.hasOwnProperty(key) )
				{
					request[key] = extra[key];
				}
			}
		}

		request.execute();
	}
};