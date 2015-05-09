<?php namespace App\Http\Controllers\Image;

use App\Models\Project_Promo;

class ProjectPromoImageController extends \App\Http\Controllers\ImageController
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
				'width' => 320,
				'height' => 240
			]
		];

		return ($size_id !== NULL ? $sizes[$size_id] : $sizes);
	}

	public static function getColumnID()
	{
		return 'image';
	}

	public static function getDirectory($project_id, $promo_id)
	{
		return \App\Models\Project_Promo::getImageDirectoryStatic($project_id, $promo_id);
	}

	public function subRender($input)
	{
		$num_input = count($input);

		$index = NULL;

		if ( $num_input === 7 )
		{
			list($project_id, $project_slug, $promo_id, $size_id, $index, $promo_slug, $extension) = $input;
		}
		elseif ( $num_input === 6 )
		{
			list($project_id, $project_slug, $promo_id, $size_id, $promo_slug, $extension) = $input;
		}
		elseif ( $num_input === 5 )
		{
			list($project_id, $project_slug, $promo_id, $promo_slug, $extension) = $input;
		}

		if ( $promo_slug === 'processing' )
		{
			return
			[
				'processing' => TRUE,
				'size_data' => self::getSizes($size_id),
				'file_extension' => $extension
			];
		}
		else
		{
			$directory = self::getDirectory($project_id, $promo_id);
			$filename = (isset($size_id) ? $promo_id . '-' . $size_id . ($index !== NULL ? '-' . $index : '') : 'original') . '.' . $extension;

			return
			[
				'processing' => FALSE,
				'path' => $directory . $filename,
				'file_extension' => $extension
			];
		}
	}
}