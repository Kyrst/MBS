<?php namespace App\Helpers\Core;

class URI
{
	const FORMAT_PROTOCOL_NONE = 'none';
	const FORMAT_PROTOCOL_HTTP = 'http';
	const FORMAT_PROTOCOL_HTTPS = 'https';
	const FORMAT_PROTOCOL_EITHER = 'either';

	public static function formatURL($url, $protocol = self::FORMAT_PROTOCOL_EITHER, $remove_protocol = FALSE)
	{
		if ( $remove_protocol === FALSE )
		{
			if ( $protocol === self::FORMAT_PROTOCOL_HTTPS )
			{
				if ( substr($url, 0, 8) !== 'https://' )
				{
					$url = 'https://' . $url;
				}
			}
			elseif ( $protocol === self::FORMAT_PROTOCOL_HTTP )
			{
				if ( substr($url, 0, 7) !== 'http://' )
				{
					$url = 'http://' . $url;
				}
			}
			elseif ( $protocol === self::FORMAT_PROTOCOL_EITHER )
			{
				if ( substr($url, 0, 8) !== 'https://' && substr($url, 0, 7) !== 'http://' )
				{
					$url = 'http://' . $url;
				}
			}
		}
		else
		{
			if ( substr($url, 0, 7) === 'http://' )
			{
				$url = substr($url, 7);
			}
			elseif ( substr($url, 0, 8) === 'https://' )
			{
				$url = substr($url, 8);
			}
		}

		return $url;
	}
}