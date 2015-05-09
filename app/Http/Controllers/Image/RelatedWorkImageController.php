<?php namespace App\Http\Controllers\Image;

use App\Models\Project_Promo;

class RelatedWorkImageController extends \App\Http\Controllers\ImageController
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
		return 'thumbnail_image';
	}

	public static function getDirectory($project_id, $related_work_item_id)
	{
		return \App\Models\Related_Work::getThumbnailImageDirectoryStatic($project_id, $related_work_item_id);
	}

	public function subRender($input)
	{
		$num_input = count($input);

		$index = NULL;

		if ( $num_input === 7 )
		{
			list($project_id, $project_slug, $related_work_item_id, $size_id, $index, $promo_slug, $extension) = $input;
		}
		elseif ( $num_input === 6 )
		{
			list($project_id, $project_slug, $related_work_item_id, $size_id, $promo_slug, $extension) = $input;
		}
		elseif ( $num_input === 5 )
		{
			list($project_id, $project_slug, $related_work_item_id, $promo_slug, $extension) = $input;
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
			$directory = self::getDirectory($project_id, $related_work_item_id);
			$filename = (isset($size_id) ? $related_work_item_id . '-' . $size_id . ($index !== NULL ? '-' . $index : '') : 'original') . '.' . $extension;

			return
			[
				'processing' => FALSE,
				'path' => $directory . $filename,
				'file_extension' => $extension
			];
		}
	}
}