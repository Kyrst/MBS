<?php namespace App\Helpers\Core;

class File
{
	public static function getBytes($value)
	{
		$value = trim($value);
		$last = strtolower($value[strlen($value) - 1]);

		switch($last)
		{
			case 'g':
				$value *= 1024;
			case 'm':
				$value *= 1024;
			case 'k':
				$value *= 1024;
		}

		return $value;
	}

	public static function formatBytes($bytes, $precision = 2)
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB');

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		$bytes /= (1 << (10 * $pow));

		return round($bytes, $precision) . ' ' . $units[$pow];
	}

	public static function getExtensionFromMime($mime)
	{
		switch( $mime )
		{
			case 'image/bmp': return 'bmp';
			case 'image/cis-cod': return 'cod';
			case 'image/gif': return 'gif';
			case 'image/ief': return 'ief';
			case 'image/jpeg': return 'jpg';
			case 'image/pipeg': return 'jfif';
			case 'image/tiff': return 'tif';
			case 'image/x-cmu-raster': return 'ras';
			case 'image/x-cmx': return 'cmx';
			case 'image/x-icon': return 'ico';
			case 'image/x-portable-anymap': return 'pnm';
			case 'image/x-portable-bitmap': return 'pbm';
			case 'image/x-portable-graymap': return 'pgm';
			case 'image/x-portable-pixmap': return 'ppm';
			case 'image/x-rgb': return 'rgb';
			case 'image/x-xbitmap': return 'xbm';
			case 'image/x-xpixmap': return 'xpm';
			case 'image/x-xwindowdump': return 'xwd';
			case 'image/png': return 'png';
			case 'image/x-jps': return 'jps';
			case 'image/x-freehand': return 'fh';
			default: return false;
		}
	}
}