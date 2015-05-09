<?php namespace App\Helpers\Core;

define('MAX_TABLE_COLUMN_SIZE', 16);

define('MANAGE_OPTION_EDIT', 1);
define('MANAGE_OPTION_DELETE', 2);

trait DynamicTable
{
	protected $dynamic_table_options;

	private function setDefaultDynamicTableOptions()
	{
		$this->dynamic_table_options =
		[
			'model' => NULL,
			'route' => NULL,
			'identifier' =>
			[
				'singular' => NULL,
				'plural' => NULL
			],
			'default_sort_column' => NULL,
			'default_sort_order' => 'asc',
			'table' =>
			[
				'columns' => [],
				'manage' =>
				[
					'soft_delete' => FALSE
				]
			],
			'search' =>
			[
				'enabled' => FALSE,
				'columns' => []
			],
			'paging' =>
			[
				'enabled' => FALSE,
				'num_per_page' => 20
			],
			'urls' =>
			[
				'get' => NULL,
				'add' => NULL
			]
		];
	}

	protected function initDynamicTable(array $options = [])
	{
		$this->setDefaultDynamicTableOptions();

		if ( is_array($options) && count($options) > 0 )
		{
			$this->dynamic_table_options = array_merge($this->dynamic_table_options, $options);
		}

		if ( !isset($this->dynamic_table_options['table']['columns']) )
		{
			throw new \Exception('Missing required dynamic table  option "columns".');
		}

		if ( !isset($this->dynamic_table_options['identifier']) )
		{
			throw new \Exception('Missing required dynamic table  option "identifier".');
		}

		if ( !isset($this->dynamic_table_options['urls']) )
		{
			throw new \Exception('Missing required dynamic table option "urls".');
		}

		$this->dynamic_table_options['table']['columns']['manage'] =
		[
			'text' => 'Manage',
			'size' => 2
		];

		$this->checkTableColumnSize();

		$dynamic_table_view = view('dashboard/partials/dynamic_table/dynamic_table_container');
		$dynamic_table_view->add_link = $this->dynamic_table_options['urls']['add'];
		$dynamic_table_view->identifier = $this->dynamic_table_options['identifier'];

		if ( \URL::current() === $this->dynamic_table_options['route'] )
		{
			$this->assign('dynamic_table', $dynamic_table_view->render());
			$this->assign('dynamic_table', $this->dynamic_table_options, \App\Http\Controllers\CoreController::SECTION_JS);
		}
	}

	private function checkTableColumnSize()
	{
		$total_size = 0;

		foreach ( $this->dynamic_table_options['table']['columns'] as $table_column_id => $table_column )
		{
			if ( !isset($table_column['size']) )
			{
				throw new \Exception('Missing required option "size" for table column "' . $table_column_id . '".');
			}

			$total_size += $table_column['size'];
		}

		if ( $total_size !== MAX_TABLE_COLUMN_SIZE )
		{
			throw new \Exception('Incorrect table column size "' . $total_size . '" instead of "' . MAX_TABLE_COLUMN_SIZE . '".');
		}
	}

	public function getDynamicTableHTML()
	{
		$called_class = get_called_class();

		if ( !method_exists($called_class, 'getDynamicTableData') )
		{
			throw new \Exception($called_class . ' is missing required dynamic table function "getDynamicTableData."');
		}

		if ( !method_exists($called_class, 'setDynamicTableColumnData') )
		{
			throw new \Exception($called_class . ' is missing required dynamic table function "setDynamicTableColumnData".');
		}

		$model = $this->dynamic_table_options['model'];
		$model::init(); // REMOVE ALL OF THESE AND PUT IN __CONSTRUCT SOMEHOW

		$default_sort_column = $this->getTableColumn($this->dynamic_table_options['default_sort_column']);

		$sort_column = \Input::get('sort_column', $default_sort_column['sort']);
		$sort_order = \Input::get('sort_order', $this->dynamic_table_options['default_sort_order']);

		$filters =
		[
			'search_query' => trim(\Input::get('search_query'))
		];

		$items = $this->getDynamicTableData($filters);

		// $items->where('name', 'LIKE', '%%'); MAKE DYNAMIC
		// $this->dynamic_table_options['search']['columns']

		$items = $items->orderBy($sort_column, $sort_order);

		//$items = $items->active();

		if ( $this->dynamic_table_options['paging'] )
		{
			$items = $items->paginate($this->dynamic_table_options['paging']['num_per_page']);
		}
		else
		{
			$items = $items->get();
		}

		$dynamic_table_column_data_result = $this->setDynamicTableColumnData($items);

		if ( $dynamic_table_column_data_result === NULL )
		{
			throw new \Exception($called_class . ' failed to initialize setDynamicTableColumnData.');
		}
		elseif ( !is_array($dynamic_table_column_data_result['items']) && !$dynamic_table_column_data_result['items'] instanceof \Illuminate\Pagination\LengthAwarePaginator )
		{
			throw new \Exception($called_class . ' did not set $dynamic_table_column_data_result[\'items\'] correctly.');
		}
		elseif ( !is_array($dynamic_table_column_data_result['table_column_data']) )
		{
			throw new \Exception($called_class . ' did not set $dynamic_table_column_data_result[\'table_column_data\'] correctly.');
		}

		$items = $dynamic_table_column_data_result['items'];
		$table_column_data = $dynamic_table_column_data_result['table_column_data'];

		$manage_dropdown_view = view('dashboard/partials/dynamic_table/manage_dropdown');

		foreach ( $table_column_data as $item_index => $item )
		{
			$manage_dropdown_view->item = $items[$item_index];

			$table_column_data[$item_index]['manage'] =
			[
				'html' => $manage_dropdown_view->render()
			];
		}

		$table_view = view('dashboard/partials/dynamic_table/dynamic_table');

		$model = $this->dynamic_table_options['model'];

		$table_view->num_total_items = $model::all()->count();

		if ( $this->dynamic_table_options['paging'] )
		{
			$table_view->num_total_filtered_items = $items->total();
			$table_view->num_page_items = count($items);

			$this->ajax->addData('paging', ['current_page' => $items->currentPage(), 'num_pages' => $items->lastPage()]);
		}
		else
		{
			$num_items = $items->count();

			$table_view->num_total_filtered_items = $num_items;
			$table_view->num_page_items = $num_items;
		}

		$table_view->identifier = $this->dynamic_table_options['identifier'];
		$table_view->table_columns = $this->dynamic_table_options['table']['columns'];
		$table_view->table_column_data = $table_column_data;
		$table_view->num_table_columns = count($this->dynamic_table_options['table']['columns']);
		$table_view->items = $items;
		$table_view->filters = $filters;
		$table_view->search_enabled = $this->dynamic_table_options['search']['enabled'];
		$table_view->paging_enabled = $this->dynamic_table_options['paging']['enabled'];

		$this->ajax->addData('html', $table_view->render());

		return $this->ajax->output();
	}

	private function getTableColumn($table_column_id)
	{
		return (isset($this->dynamic_table_options['table']['columns'][$table_column_id]) ? $this->dynamic_table_options['table']['columns'][$table_column_id] : NULL);
	}

	public static function convertTableColumnSize($size)
	{
		switch ( $size )
		{
			case 1: return 'one';
			case 2: return 'two';
			case 3: return 'three';
			case 4: return 'four';
			case 5: return 'five';
			case 6: return 'six';
			case 7: return 'seven';
			case 8: return 'eight';
			case 9: return 'nine';
			case 10: return 'ten';
			case 11: return 'eleven';
			case 12: return 'twelwe';
			case 13: return 'thirteen';
			case 14: return 'fourteen';
			case 15: return 'fifteen';
			case 16: return 'sixteen';
		}
	}
}