function Core_URI() {}

Core_URI.prototype =
{
	PROTOCOL_NONE: 'none',
	PROTOCOL_HTTP: 'http',
	PROTOCOL_HTTPS: 'https',
	PROTOCOL_EITHER: 'either',

	redirect: function(url, with_base_url)
	{
		window.location = (typeof with_base_url === 'boolean' ? this.urlize(url) : url);
	},

	urlize: function(url)
	{
		if ( $core.isUndefined($core.options.uri.base_url) )
		{
			$core.log('Base URL not set.');

			return;
		}

		return $core.options.uri.base_url + url;
	},

	formatURI: function(url, protocol, remove_protocol)
	{
		protocol = protocol || this.PROTOCOL_EITHER;
		remove_protocol = remove_protocol || false;

		if ( remove_protocol === false )
		{
			if ( protocol === this.PROTOCOL_HTTPS )
			{
				if ( url.substring(0, 8) !== 'https://' )
				{
					url = 'https://' + url;
				}
			}
			else if ( protocol === this.PROTOCOL_HTTP )
			{
				if ( url.substring(0, 7) !== 'http://' )
				{
					url = 'http://' + url;
				}
			}
			else if ( protocol === this.PROTOCOL_EITHER )
			{
				if ( url.substring(0, 8) !== 'https://' && url.substring(0, 7) !== 'http://' )
				{
					url = 'http://' + url;
				}
			}
		}
		else
		{
			if ( url.substring(0, 7) !== 'http://' )
			{
				url = url.substring(7);
			}
			else if ( url.substring(0, 8) !== 'https://' )
			{
				url = url.substring(8);
			}
		}

		return url;
	}
};