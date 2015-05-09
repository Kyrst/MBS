<?php namespace App\Helpers\Core;

class Markup
{
	public static function segmentLoadingContainer($id, $loading_text = 'Loading', $color = null)
	{
		return '<div id="' . $id . '" class="ui segment loading-container is-loading' . ($color !== null ? ' ' . $color : '') . '"><div id="' . $id . '_loader" class="ui active inverted dimmer"><div class="ui small loader'. ($loading_text !== null ? ' text' : '') . '">' . ($loading_text !== null ? $loading_text : '') . '</div></div></div>';
	}

	public static function noImageURL()
	{
		return asset('libs/bower/semantic/examples/images/wireframe/image.png');
	}

	public static function noImage($size = NULL, $rounded = FALSE)
	{
		return '<img src="' . self::noImageURL() . '" class="ui image' . ($size !== NULL ? ' ' . $size : '') . ($rounded === TRUE ? ' rounded' : '') . '" alt="">';
	}
}