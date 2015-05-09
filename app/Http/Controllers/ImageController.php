<?php namespace App\Http\Controllers;

use Intervention\Image\ImageManagerStatic;

abstract class ImageController extends Controller
{
	const DEFAULT_CANVAS_BACKGROUND_COLOR = '#FFFFFF';

	public static function deleteImage($reference_id)
	{
		$called_class = get_called_class();
	}

	public static function generateSizes($reference, \Intervention\Image\Image $original_image, array $image_data, $directory)
	{
		$called_class = get_called_class();

		if ( !method_exists($called_class, 'getSizes') )
		{
			throw new \Exception($called_class . ' is missing required function "getSizes".');
		}

		if ( !method_exists($called_class, 'getColumnID') )
		{
			throw new \Exception($called_class . ' is missing required function "getColumnID".');
		}

		foreach ( $called_class::getSizes() as $size_id => $size_data )
		{
			$generated_image = clone $original_image;

			$generated_image = self::generateSize($generated_image, $size_data);

			$filename = $reference->id . '-' . $size_id . ($image_data['index'] > 0 ? '-' . $image_data['index'] : '') . '.' . $image_data['file_extension'];
			$path = $directory . $filename;

			$generated_image->save($path, 100);

			if ( $image_data['index'] > 0 )
			{
				$old_filename = $reference->id . '-' . $size_id . ($image_data['index'] > 1 ? '-' . ($image_data['index'] - 1) : '') . '.' . $image_data['file_extension'];
				$old_path = $directory . $old_filename;

				if ( file_exists($old_path) )
				{
					unlink($old_path);
				}
			}

			$generated_image->destroy();
		}
	}

	private static function generateSize(\Intervention\Image\Image $image, $size_data)
	{
		$image->resize((isset($size_data['width']) ? $size_data['width'] : NULL), (isset($size_data['height']) ? $size_data['height'] : NULL), function ($constraint) use ($size_data)
		{
			if ( !isset($size_data['proportional']) || $size_data['proportional'] === TRUE )
			{
				$constraint->aspectRatio();
			}

			if ( !isset($size_data['upsize']) || $size_data['upsize'] === FALSE )
			{
				$constraint->upsize();
			}
		});

		if ( isset($size_data['crop']) )
		{
			$image->crop($size_data['crop']['width'], $size_data['crop']['height'], $size_data['crop']['x'], $size_data['crop']['y']);
		}

		if ( isset($size_data['canvas']) )
		{
			$image->resizeCanvas((isset($size_data['canvas']['width']) ? $size_data['canvas']['width'] : NULL), (isset($size_data['canvas']['height']) ? $size_data['canvas']['height'] : NULL), 'center', FALSE, (isset($size_data['canvas']['background_color']) ? $size_data['canvas']['background_color'] : self::DEFAULT_CANVAS_BACKGROUND_COLOR));
		}

		return $image;
	}

	public function render()
	{
		$called_class = get_called_class();

		$image_data = $called_class::subRender(func_get_args());

		$headers =
		[
			'Cache-Control' => 'must-revalidate',
			'Pragma' => 'public'
		];

		if ( $image_data['processing'] === FALSE )
		{
			$lifetime = 0;

			if ( file_exists($image_data['path']) )
			{
				$file_modified_time = filemtime($image_data['path']);
				$etag = md5($file_modified_time . $image_data['path']);
				$time = gmdate('r', $file_modified_time);
				$expires = gmdate('r', $file_modified_time + $lifetime);

				$headers['Last-Modified'] = $time;
				$headers['Expires'] = $expires;
				$headers['Etag'] = $etag;

				$if_modified_since_header = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] === $time);
				$if_none_match_header = (isset($_SERVER['HTTP_IF_NONE_MATCH']) && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) === $etag);

				if ( $if_modified_since_header || $if_none_match_header )
				{
					return \Response::make('', 304, $headers);
				}

				$image_raw = ImageManagerStatic::make($image_data['path'])->response($image_data['file_extension'])->original;
				$image_size = strlen($image_raw);
			}
			else
			{
				$image_raw = ImageManagerStatic::make(public_path('libs/bower/semantic/examples/images/wireframe/image.png'));

				$image_raw = self::generateSize($image_raw, $image_data['size_data'])->response($image_data['file_extension'])->original;
				$image_size = strlen($image_raw);
			}
		}
		else
		{
			$image_raw = ImageManagerStatic::canvas(800, 600, '#CCCCCC');

			$image_raw = self::generateSize($image_raw, $image_data['size_data'])->response($image_data['file_extension'])->original;
			$image_size = strlen($image_raw);
		}

		$headers = array_merge
		(
			$headers,
			array
			(
				'Content-Type' => 'image/jpeg',
				'Content-Length' => $image_size,
			)
		);

		return \Response::make($image_raw, 200, $headers);
	}
}