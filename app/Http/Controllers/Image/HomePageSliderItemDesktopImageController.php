<?php namespace App\Http\Controllers\Image;

class HomePageSliderItemDesktopImageController extends \App\Http\Controllers\ImageController
{
	const SIZE_DASHBOARD_PREVIEW = 1;
	const SIZE_FRONT = 2;

	public static function getSizes($size_id = NULL)
	{
		$sizes =
		[
			self::SIZE_DASHBOARD_PREVIEW =>
			[
				'height' => 66,
				'canvas' =>
				[
					'width' => 80,
					'height' => 66
				]
			],
			self::SIZE_FRONT =>
			[
				'width' => 200,
				'height' => 200
			]
		];

		return ($size_id !== NULL ? $sizes[$size_id] : $sizes);
	}

	public static function getColumnID()
	{
		return 'desktop_image';
	}

	public static function getDirectory($id)
	{
		return \App\Models\Home_Page_Slider_Item::getDesktopImageDirectory($id);
	}

	public function subRender($input)
	{
		$num_input = count($input);

		$index = NULL;

		if ( $num_input === 2 )
		{
			list($id, $extension) = $input;
		}
		elseif ( $num_input === 3 )
		{
			list($size_id, $id, $extension) = $input;
		}

		$directory = self::getDirectory($id);
		$filename = (isset($size_id) ? $id . '-' . $size_id . ($index !== NULL ? '-' . $index : '') : 'original') . '.' . $extension;

		return
		[
			'processing' => ($id === 'processing'),
			'size_data' => (isset($size_id) ? self::getSizes($size_id) : NULL),
			'path' => $directory . $filename,
			'file_extension' => $extension
		];
	}
}