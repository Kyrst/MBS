<?php namespace App\Helpers\Core;

class Str extends \Illuminate\Support\Str
{
	public static function camelCaseToHyphen($str)
	{
		return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $str));
	}

	public static function slashToHyphen($str)
	{
		return strtolower(str_replace('/', '-', $str));
	}

	public static function slashToUnderscore($str)
	{
		return strtolower(str_replace('/', '_', $str));
	}

	public static function camelCaseToUnderscore($str)
	{
		return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $str));
	}

	public static function hyphenToUnderscore($str)
	{
		return strtolower(str_replace('-', '_', $str));
	}
}