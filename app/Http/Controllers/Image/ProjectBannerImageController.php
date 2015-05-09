<?php namespace App\Http\Controllers\Image;

class ProjectBannerImageController extends \App\Http\Controllers\ImageController
{
	const SIZE_DASHBOARD_DYNAMIC_ITEM = 1;
	const SIZE_FRONT = 2;

	public static function getSizes($size_id = NULL)
	{
		$sizes =
		[
			self::SIZE_DASHBOARD_DYNAMIC_ITEM =>
			[
				'height' => 156,
				'canvas' =>
				[
					'width' => 189,
					'height' => 156
				]
			],
			self::SIZE_FRONT =>
			[
				'width' => 1600
			]
		];

		return ($size_id !== NULL ? $sizes[$size_id] : $sizes);
	}

	public static function getColumnID()
	{
		return 'banner';
	}

	public static function getDirectory($project_id)
	{
		return \App\Models\Project::getDynamicImageDirectory($project_id, 'banner');
	}

	public function subRender($input)
	{
		$num_input = count($input);

		$index = NULL;

		if ( $num_input === 5 )
		{
			list($project_id, $size_id, $index, $project_slug, $extension) = $input;
		}
		elseif ( $num_input === 4 )
		{
			list($project_id, $size_id, $project_slug, $extension) = $input;
		}
		elseif ( $num_input === 3 )
		{
			list($project_id, $project_slug, $extension) = $input;
		}

		$directory = self::getDirectory($project_id);
		$filename = (isset($size_id) ? $project_id . '-' . $size_id . ($index !== NULL ? '-' . $index : '') : 'original') . '.' . $extension;

		if ( $project_slug === 'processing' )
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
			return
			[
				'processing' => FALSE,
				'path' => $directory . $filename,
				'file_extension' => $extension
			];
		}
	}
}
