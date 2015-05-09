<?php namespace App\Helpers;

class Vimeo
{
	public static function getURL($vimeo_id)
	{
		return 'http://vimeo.com/' . $vimeo_id;
	}
}