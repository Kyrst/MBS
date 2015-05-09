function Core_Ajax_Request() {}

Core_Ajax_Request.prototype =
{
	$form: null,

	url: '',
	data: {},
	method: 'GET',
	data_type: 'json',
	cache: false,
	async: true,

	result: null,

	before: null,
	progress: null,
	success: null,
	error: null,
	always: null,
	abort: null,

	execute: function()
	{
		var inst = this;

		if ( $core.isUndefined(inst.url) )
		{
			$core.log('Missing AJAX URL.');

			return;
		}

		if ( !$core.isUndefined($core.ajax.requests[inst.url]) )
		{
			$core.ajax.requests[inst.url].abort();
		}

		var file_upload = ($core.isDefined(inst.file_upload) && inst.file_upload === true);

		var headers = { 'X-XSRF-TOKEN': $core.options.csrf_token };

		if ( file_upload === true && $core.isObject(inst.file_upload_data) )
		{
			headers = $.extend(headers, inst.file_upload_data);
		}

		$core.ajax.requests[inst.url] = $.ajax(
		{
			type: inst.method,
			url: inst.url,
			data: inst.data,
			dataType: inst.data_type,
			cache: inst.cache,
			async: inst.async,
			processData: (file_upload !== true),
			contentType: (file_upload === true ? false : 'application/x-www-form-urlencoded; charset=UTF-8'),
			headers: headers,
			xhr: function()
			{
				var xhr = $.ajaxSettings.xhr();

				if ( $core.isFunction(inst.progress) )
				{
					xhr.upload.onprogress = function(e)
					{
						var percent = (e.loaded / e.total) * 100;

						inst.progress(percent);
					};
				}

				return xhr;
			},
			beforeSend: function(xhr, data)
			{
				if ( $core.isFunction(inst.before) )
				{
					inst.before(xhr, data);
				}
			}
		}).done(function(result)
		{
			if ( $core.isFunction(inst.success) )
			{
				inst.success(result);
			}

			if ( typeof result.message !== 'undefined' && result.message !== null )
			{
				if ( typeof result.message.type === 'number' && typeof result.message.text === 'string' )
				{
					$core.ui.message.engine.show(result.message.type, result.message.text);
				}
			}

			if ( !$core.isUndefined(result.redirect) )
			{
				setTimeout(function()
				{
					return $core.uri.redirect(result.redirect.url);
				}, result.redirect.delay);
			}

			inst.result = result;
		}).always(function(result)
		{
			if ( $core.isFunction(inst.always) )
			{
				inst.always(result);
			}

			delete $core.ajax.requests[inst.url];
		}).fail(function(xhr, textStatus, errorThrown)
		{
			if ( xhr === 'abort' )
			{
				if ( $core.isFunction(inst.abort) )
				{
					inst.abort();
				}
			}
			else
			{
				if ( $core.isFunction(inst.error) )
				{
					inst.error(errorThrown);
				}
			}
		});
	}
};