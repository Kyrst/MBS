<?php namespace App\Http\Controllers\Image;

class ProjectThumbnailImageController extends \App\Http\Controllers\ImageController
{
	const SIZE_DASHBOARD_DYNAMIC_TABLE = 1;
	const SIZE_DASHBOARD_DYNAMIC_ITEM = 2;
	const SIZE_FRONT = 3;

	public static function getSizes($size_id = NULL)
	{
		$sizes =
		[
			self::SIZE_DASHBOARD_DYNAMIC_TABLE =>
			[
				'height' => 17,
				'canvas' =>
				[
					'width' => 20,
					'height' => 17
				]
			],
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
				'width' => 404,
				'height' => 273
			]
		];

		return ($size_id !== NULL ? $sizes[$size_id] : $sizes);
	}

	public static function getColumnID()
	{
		return 'thumbnail';
	}

	public static function getDirectory($project_id)
	{
		return \App\Models\Project::getDynamicImageDirectory($project_id, 'thumbnail');
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
