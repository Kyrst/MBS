<?php namespace App\Helpers\Core;

class Directory
{
	public static function delete($dir, $recursively = FALSE)
	{
		if ( !file_exists($dir) )
		{
			return;
		}

		$files = array_diff(scandir($dir), ['.', '..']);

		foreach ( $files as $file )
		{
			$path = $dir . '/' . $file;

			if ( is_dir($path) && $recursively === TRUE )
			{
				self::deleteDirectory($path, TRUE);
			}
			else
			{
				unlink($path);
			}
		}

		if ( file_exists($dir) )
		{
			rmdir($dir);
		}
	}
}