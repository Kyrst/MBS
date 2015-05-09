<?php namespace App\Helpers\Core;

trait DynamicModel
{
	protected static $options =
	[
		'columns' => [],
		'slug' => NULL,
		'active_toggle' => NULL
	];

	private static $types_to_trim =
	[
		'text',
		'textarea'
	];

	private static $types_with_custom_saving =
	[
		'image'
	];

	public static function getOption($option)
	{
		return self::$options[$option];
	}

	private static function assignSaveData(array $input, $called_class)
	{
		$save_data = [];

		foreach ( $input as $input_key => $input_value )
		{
			if ( !isset(self::$options['columns'][$input_key]) )
			{
				continue;
			}

			$column_data = self::$options['columns'][$input_key];

			if ( (isset($column_data['nullable']) && $column_data['nullable'] === TRUE) && empty($input_value) )
			{
				$input_value = NULL;
			}
			else
			{
				if ( in_array($column_data['form']['type'], self::$types_to_trim) )
				{
					$input_value = trim($input_value);
				}
			}

			if ( !in_array($column_data['form']['type'], self::$types_with_custom_saving) )
			{
				$save_data[$input_key] = $input_value;
			}
		}

		if ( self::$options['slug'] !== NULL )
		{
			if ( !isset($save_data[self::$options['slug']]) )
			{
				throw new \Exception($called_class . ' is trying to slugify column "' . self::$options['slug'] . '" which is not defined.');
			}

			$save_data['slug'] = \Illuminate\Support\Str::slug($save_data[self::$options['slug']]);
		}

		if ( self::$options['active_toggle'] !== NULL )
		{
			$save_data[self::$options['active_toggle']['column']] = (isset($input[self::$options['active_toggle']['column']]) && $input[self::$options['active_toggle']['column']] === '1' ? 'yes' : 'no');
		}

		$called_class = get_called_class();

		if ( method_exists($called_class, 'postAssignSaveData') )
		{
			$save_data = self::postAssignSaveData($save_data, $input);
		}

		return $save_data;
	}

	public static function init()
	{
		$called_class = get_called_class();

		if ( !method_exists($called_class, 'initDynamicModel') )
		{
			throw new \Exception($called_class . ' is missing required dynamic model function initDynamicModel.');
		}

		self::$options = self::initDynamicModel();

		if ( !method_exists($called_class, 'getDynamicItemColumns') )
		{
			throw new \Exception($called_class . ' is missing required dynamic model function getDynamicItemColumns.');
		}

		self::$options['columns'] = $called_class::getDynamicItemColumns();
	}

	private static function initSaveData($input)
	{
		self::init();

		$called_class = get_called_class();

		$save_data = self::assignSaveData($input, $called_class);

		return
		[
			'called_class' => $called_class,
			'save_data' => $save_data
		];
	}

	public static function add(array $input)
	{
		$data = self::initSaveData($input);

		$item = new $data['called_class'];

		foreach ( self::$options['columns'] as $column_id => $column_data )
		{
			if ( in_array($column_data['form']['type'], self::$types_with_custom_saving) || empty($column_data['column']) )
			{
				continue;
			}

			$item->$column_data['column'] = $data['save_data'][$column_id];
		}

		if ( self::$options['slug'] !== NULL )
		{
			$item->slug = $data['save_data']['slug'];
		}

		if ( self::$options['active_toggle'] !== NULL )
		{
			$active_toggle_column = self::$options['active_toggle']['column'];

			$item->$active_toggle_column = $data['save_data'][$active_toggle_column];
		}

		$item->save();

		if ( method_exists($data['called_class'], 'postAdd') )
		{
			self::postAdd($item, $data['save_data']);
		}

		return $item;
	}

	public function edit(array $input)
	{
		$data = self::initSaveData($input);

		foreach ( self::$options['columns'] as $column_id => $column_data )
		{
			if ( in_array($column_data['form']['type'], self::$types_with_custom_saving) || empty($column_data['column']) )
			{
				continue;
			}

			$this->$column_data['column'] = $data['save_data'][$column_id];
		}

		if ( self::$options['slug'] !== NULL )
		{
			$this->slug = $data['save_data']['slug'];
		}

		if ( self::$options['active_toggle'] !== NULL )
		{
			$active_toggle_column = self::$options['active_toggle']['column'];

			$this->$active_toggle_column = $data['save_data'][$active_toggle_column];
		}

		$this->save();

		if ( method_exists($data['called_class'], 'postEdit') )
		{
			$this->postEdit($data['save_data']);
		}
	}

	public function isActive()
	{
		$active_toggle = self::$options['active_toggle'];

		if ( $active_toggle === NULL )
		{
			$called_class = get_called_class();

			throw new \Exception($called_class . ' does not have an active column set.');
		}

		return $this->$active_toggle['column'];
	}

	public function scopeActive($query)
	{
		$active_toggle = self::$options['active_toggle'];

		if ( $active_toggle === NULL )
		{
			$called_class = get_called_class();

			throw new \Exception($called_class . ' does not have an active column set.');
		}

		return $query->where($active_toggle['column'], 'yes');
	}

	public function getDynamicImage($column_id)
	{
		$called_class = get_called_class();

		$image = json_decode($this->$column_id, TRUE);

		if ( $image === null )
		{
			return null;
		}

		$image['processing'] = ($image['processing'] === 'yes');
		$image['path'] = $called_class::getDynamicImageDirectory($this->id, $column_id) . 'original.' . $image['file_extension'];
		$image['original_url'] = $this->getDynamicImageURL($column_id);

		return $image;
	}

	public function haveDynamicImage($column_id)
	{
		return ($this->$column_id !== NULL);
	}

	public function getDynamicImageURL($column_id, $size_id = NULL)
	{
		if ( !self::haveDynamicImage($column_id) )
		{
			return \App\Helpers\Core\Markup::noImageURL();
		}

		$called_class = get_called_class();

		$called_class::init();

		$image_url_template = self::$options['columns'][$column_id]['url'];

		$image_data = json_decode($this->$column_id, TRUE);

		$from =
		[
			($size_id !== NULL ? '{size}' : '/{size}'),
			($size_id === NULL || $image_data['index'] === 0 ? '{index}/' : ''),
			'{id}',
			'{size}',
			'{index}',
			'{slug}',
			'{file_extension}'
		];

		$to =
		[
			($size_id !== NULL ? $size_id : ''),
			($size_id === NULL || $image_data['index'] === 0 ? '' : ''),
			$this->id,
			$size_id,
			$image_data['index'],
			($image_data['processing'] === 'yes' && $size_id !== NULL ? 'processing' : $this->slug),
			$image_data['file_extension']
		];

		$image_url = str_replace($from, $to, $image_url_template);

		return $image_url;
	}

	public function setDynamicImage($column_id, $file_extension, $mime)
	{
		$current_image_data = ($this->$column_id !== NULL ? json_decode($this->$column_id, TRUE) : NULL);

		if ( $current_image_data !== NULL )
		{
			$index = (int)$current_image_data['index'] + 1;
		}
		else
		{
			$index = 0;
		}

		$image_data =
		[
			'file_extension' => $file_extension,
			'mime' => $mime,
			'index' => $index,
			'processing' => 'yes'
		];

		$this->$column_id = json_encode($image_data);
		$this->save();
	}
}