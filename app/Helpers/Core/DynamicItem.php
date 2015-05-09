<?php namespace App\Helpers\Core;

use App\Http\Controllers\CoreController;
use App\Models\Image_Generation_Queue;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait DynamicItem
{
	protected $dynamic_item_options;

	private function setDefaultDynamicItemOptions()
	{
		$this->dynamic_item_options =
		[
			'model' => NULL,
			'identifier' =>
			[
				'singular' => NULL,
				'plural' => NULL
			],
			'title_column' => NULL,
			'save' =>
			[
				'add' =>
				[
					'success' =>
					[
						'message' => NULL,
						'redirect' => NULL
					]
				],
				'edit' =>
				[
					'success' =>
					[
						'message' => NULL,
						'redirect' => NULL
					]
				]
			],
			'item_not_found' =>
			[
				'message' => NULL,
				'redirect' => NULL
			],
			'breadcrumb_items' =>
			[
				'pre' => NULL
			],
			'tabs' => NULL
		];
	}

	protected function initDynamicItem(array $options = [])
	{
		$this->setDefaultDynamicItemOptions();

		if ( is_array($options) && count($options) > 0 )
		{
			$this->dynamic_item_options = array_merge($this->dynamic_item_options, $options);
		}

		if ( !isset($this->dynamic_item_options['title_column']) )
		{
			throw new \Exception('Missing required dynamic item option "title_column".');
		}

		if ( !isset($this->dynamic_item_options['save']) )
		{
			throw new \Exception('Missing required dynamic item option "save".');
		}

		$model = $this->dynamic_item_options['model'];

		if ( !method_exists($model, 'getDynamicItemColumns') )
		{
			throw new \Exception($model . ' is missing required dynamic model function getDynamicItemColumns.');
		}

		foreach ( $model::getDynamicItemColumns() as $column_id => $column )
		{
			if ( !isset($column['title']) )
			{
				throw new \Exception('Column "' . $column_id . '" in ' . $model . ' is missing required title.');
			}

			if ( !isset($column['form']['type']) )
			{
				throw new \Exception('Column "' . $column_id . '" in ' . $model . ' is missing required form type.');
			}
		}
	}

	public function dynamicItem($id = NULL)
	{
		$called_class = get_called_class();

		$model = $this->dynamic_item_options['model'];
		$model::init();

		if ( $id !== NULL )
		{
			try
			{
				$title_column = $this->dynamic_item_options['title_column'];

				$item_to_edit = $model::where('id', $id)
					->firstOrFail();
			}
			catch ( ModelNotFoundException $e )
			{
				$this->ui->showWarning(sprintf($this->dynamic_item_options['item_not_found']['message'], $id));

				return \Redirect::to($this->dynamic_item_options['item_not_found']['redirect']);
			}
		}
		else
		{
			$item_to_edit = NULL;
		}

		$this->assign('item_to_edit', $item_to_edit);
		$this->assign('item_id_to_edit', ($item_to_edit !== NULL ? $item_to_edit->id : NULL), CoreController::SECTION_JS);

		$this->assign('title_column', $this->dynamic_item_options['title_column']);
		$this->assign('identifier', $this->dynamic_item_options['identifier']);

		$form_html_view_filename = $this->current_controller['underscore'] . '/' . $this->dynamic_item_options['identifier']['singular'];
		$form_html_view_filepath = base_path('resources/templates/' . $form_html_view_filename);

		$form_html = NULL;

		if ( file_exists($form_html_view_filepath . '.php') )
		{
			$form_html_view = view($form_html_view_filename);
			$form_html_view->dynamic_item = $this;
			$form_html_view->item_to_edit = $item_to_edit;

			if ( method_exists($called_class, 'getDynamicItemFormHTML') )
			{
				$form_html_view = $this->getDynamicItemFormHTML($form_html_view, $item_to_edit);
			}

			$form_html = $form_html_view->render();
		}

		$this->assign('dynamic_item_columns', $model::getDynamicItemColumns(), 'js');

		$this->assign('form_html', $form_html);

		if ( is_array($this->dynamic_item_options['breadcrumb_items']['pre']) )
		{
			foreach ( $this->dynamic_item_options['breadcrumb_items']['pre'] as $breadcrumb_item )
			{
				$this->addBreadcrumbItem($breadcrumb_item['text'], $breadcrumb_item['url']);
			}
		}

		$this->addBreadcrumbItem($item_to_edit !== NULL ? e($item_to_edit->$title_column) : 'Add');

		// Tabs
		if ( is_array($this->dynamic_item_options['tabs']) )
		{
			$tabs = $this->dynamic_item_options['tabs'];

			foreach ( $tabs as $tab_id => $tab_data )
			{
				$tab_view_filename = $this->current_controller['underscore'] . '/' . $this->dynamic_item_options['identifier']['singular'] . '/tabs/' . $tab_id;
				$tab_view_filepath = base_path('resources/templates/' . $tab_view_filename);

				if ( !file_exists($tab_view_filepath . '.php') )
				{
					throw new \Exception($called_class . ' is missing tab file "' . $tab_view_filename . '.php".');
				}

				$tab_view = view($tab_view_filename);
				$tab_view->dynamic_item = $this;
				$tab_view->item_to_edit = $item_to_edit;

				if ( method_exists($called_class, 'getDynamicItemTabHTML') )
				{
					$tab_view = $this->getDynamicItemTabHTML($tab_view, $item_to_edit, $tab_id);
				}

				$tabs[$tab_id] =
				[
					'text' => $tab_data['text'],
					'only_edit' => (isset($tab_data['only_edit']) && $tab_data['only_edit'] === TRUE),
					'active_toggle' => (isset($tab_data['active_toggle']) && $tab_data['active_toggle'] === TRUE),
					'save_button' => (isset($tab_data['save_button']) && $tab_data['save_button'] === TRUE),
					'html' => $tab_view->render()
				];
			}

			$this->assign('tabs', $tabs);
		}

		$this->assign('dynamic_item', $this);

		// Active toggle
		$active_toggle = $model::getOption('active_toggle');

		if ( $active_toggle !== NULL )
		{
			$active_toggle_column = $active_toggle['column'];

			$active_toggle_view = view ('dashboard/partials/dynamic_item/form_fields/toggle');
			$active_toggle_view->name = $active_toggle_column;
			$active_toggle_view->title = $active_toggle['title'];
			$active_toggle_view->checked = ($item_to_edit !== NULL && $item_to_edit->$active_toggle_column === 'yes');

			$this->assign('active_toggle', $active_toggle_view->render());
		}

		$this->setPage($this->dynamic_item_options['identifier']['singular']);

		$this->assign('dynamic_item_options', $this->dynamic_item_options, 'js');

		$this->assign('ALWAYS_REQUIRED', ALWAYS_REQUIRED, self::SECTION_JS);
		$this->assign('REQUIRED_ON_ADD', REQUIRED_ON_ADD, self::SECTION_JS);
		$this->assign('NOT_REQUIRED', NOT_REQUIRED, self::SECTION_JS);

		return $this->display([($item_to_edit !== NULL ? e($item_to_edit->$title_column) : 'Add'), ucfirst($this->dynamic_item_options['identifier']['plural']), 'Work'], TRUE, 'dashboard/partials/dynamic_item/dynamic_item');
	}

	public function getDynamicItemColumn($column_id)
	{
		$model = $this->dynamic_item_options['model'];

		$columns = $model::getDynamicItemColumns();

		if ( !isset($columns[$column_id]) )
		{
			throw new \Exception('Could not find column "' . $column_id . '" in ' . $model . '.');
		}

		return $columns[$column_id];
	}

	public function getFormFieldHTML($column_id, $item_to_edit, $params = NULL)
	{
		$column_data = self::getDynamicItemColumn($column_id);

		$form_field_view = view('dashboard/partials/dynamic_item/form_fields/' . $column_data['form']['type']);
		$form_field_view->column_id = $column_id;
		$form_field_view->column_data = $column_data;
		$form_field_view->item_to_edit = $item_to_edit;
		$form_field_view->params = $params;

		if ( isset($params['options']) )
		{
			$form_field_view->num_options = count($params['options']);
			$form_field_view->options = $params['options'];

			if ( $column_data['form']['type'] === 'select' && isset($params['selected_option']) )
			{
				$form_field_view->selected_option = $params['selected_option'];
			}
			elseif ( $column_data['form']['type'] === 'checkbox' && isset($params['selected_options']) )
			{
				$form_field_view->selected_options = $params['selected_options'];
			}
		}

		return $form_field_view->render();
	}

	public function saveDynamicItem($id = NULL)
	{
		$called_class = get_called_class();

		$input = \Input::all();

		$have_custom_saving_columns = ($input['have_custom_saving_columns'] === 'yes');
		$items_to_delete = (isset($input['items_to_delete']) ? $input['items_to_delete'] : []);

		$model = $this->dynamic_item_options['model'];
		$dynamic_item_title_column = $this->dynamic_item_options['title_column'];

		if ( $id !== NULL )
		{
			try
			{
				$item_to_edit = $model::where('id', $id)->firstOrFail();

				if ( method_exists($called_class, 'dynamicItemPreEdit') )
				{
					$this->dynamicItemPreEdit($item_to_edit);
				}

				// Delete
				foreach ( $items_to_delete as $item_to_delete_column_id )
				{
					$this->deleteDynamicItemColumn($item_to_edit, $item_to_delete_column_id);
				}

				// Edit
				$item_to_edit->edit($input);

				if ( method_exists($called_class, 'dynamicItemPostEdit') )
				{
					$this->dynamicItemPostEdit($item_to_edit);
				}

				$success_message = sprintf($this->dynamic_item_options['save']['edit']['success']['message'], $item_to_edit->$dynamic_item_title_column);

				if ( $have_custom_saving_columns === TRUE )
				{
					$this->ui->showSuccess($success_message);
				}
				else
				{
					$this->ajax->showSuccess($success_message);

					return $this->ajax->redirect((isset($this->dynamic_item_options['save']['edit']['success']['redirect']) ? $this->dynamic_item_options['save']['edit']['success']['redirect'] : ''), 750);
				}
			}
			catch ( \Illuminate\Database\Eloquent\ModelNotFoundException $e )
			{
				$this->ajax->showWarning(sprintf($this->dynamic_item_options['item_not_found']['message'], $id));

				return $this->ajax->output();
			}
		}
		else
		{
			$added_item = $model::add($input);

			if ( method_exists($called_class, 'dynamicItemPostAdd') )
			{
				$this->dynamicItemPostAdd($added_item);
			}

			$this->ajax->addData('added_item_id', $added_item->id);

			$success_message = sprintf($this->dynamic_item_options['save']['add']['success']['message'], $added_item->$dynamic_item_title_column);

			if ( $have_custom_saving_columns === TRUE )
			{
				$this->ui->showSuccess($success_message);
			}
			else
			{
				$this->ajax->showSuccess($success_message);

				return $this->ajax->redirect($this->dynamic_item_options['save']['add']['success']['redirect'], 750);
			}
		}

		return $this->ajax->output();
	}

	public function deleteDynamicItemColumn($item_to_edit, $column_id)
	{
		$model = $this->dynamic_item_options['model'];

		$columns = $model::getDynamicItemColumns();
		$column_to_delete = $columns[$column_id];

		if ( $column_to_delete['form']['type'] === 'image' )
		{
			$image_directory = $model::getDynamicImageDirectory($item_to_edit->id, $column_id);

			\App\Helpers\Core\Directory::delete($image_directory, TRUE);

			$item_to_edit->$column_id = null;
			$item_to_edit->save();
		}
	}

	public function deleteDynamicItem($id)
	{
		$model = $this->dynamic_item_options['model'];

		$id = \Input::get('id');

		try
		{
			$item_to_delete = $model::where('id', $id)->firstOrFail();
			$item_to_delete->delete();
		}
		catch ( ModelNotFoundException $e )
		{
			$this->ui->showWarning(sprintf($this->dynamic_item_options['item_not_found']['message'], $id));

			return \Redirect::to($this->dynamic_item_options['item_not_found']['redirect']);
		}

		return $this->ajax->output();
	}

	public function uploadDynamicItemImage()
	{
		$id = $_SERVER['HTTP_ID'];
		$mime = $_SERVER['HTTP_MIME'];
		$column_id = $_SERVER['HTTP_COLUMN_ID'];

		$model = $this->dynamic_item_options['model'];

		if ( !method_exists($model, 'getDynamicImageDirectory') )
		{
			throw new \Exception($model . ' is missing required function "getDynamicImageDirectory".');
		}

		if ( !method_exists($model, 'setDynamicImage') )
		{
			throw new \Exception($model . ' is missing required function "setDynamicImage".');
		}

		$called_class = get_called_class();

		$column_options = $called_class::getDynamicItemColumn($column_id);

		try
		{
			$item = $model::where('id', $id)->firstOrFail();

			$upload_dir = $model::getDynamicImageDirectory($item->id, $column_id);

			if ( !file_exists($upload_dir) )
			{
				mkdir($upload_dir, 0775, TRUE);
			}

			$file_extension = \App\Helpers\Core\File::getExtensionFromMime($mime);
			$upload_filename = 'original.' . $file_extension;
			$upload_filepath = $upload_dir . $upload_filename;

			$input_fp = fopen('php://input', 'rb');
			$output_fp = fopen($upload_filepath, 'wb');

			while ( $chunk = fread($input_fp, 8192) )
			{
				fwrite($output_fp, $chunk);
			}

			fclose($input_fp);
			fclose($output_fp);

			Image_Generation_Queue::add($column_options['image_generation_queue'], $item->id);

			$item->setDynamicImage($column_id, $file_extension, $mime);
		}
		catch ( ModelNotFoundException $e )
		{
		}

		return \Response::json();
	}
}