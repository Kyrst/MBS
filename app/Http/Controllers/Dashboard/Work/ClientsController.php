<?php namespace App\Http\Controllers\Dashboard\Work;

use App\Http\Controllers\Image\ClientLogoImageController;
use App\Models\User_Action;

class ClientsController extends \App\Http\Controllers\Dashboard\WorkController
{
	use \App\Helpers\Core\DynamicTable, \App\Helpers\Core\DynamicItem;
	
	function __construct()
	{
		parent::__construct();

		$this->initDynamicTable
		(
			[
				'route' => \URL::route('dashboard/work/clients'),
				'model' => '\App\Models\Client',
				'identifier' =>
				[
					'singular' => 'client',
					'plural' => 'clients'
				],
				'default_sort_column' => 'name',
				'default_sort_order' => 'asc',
				'table' =>
				[
					'columns' =>
					[
						'logo' =>
						[
							'text' => '',
							'size' => 1
						],
						'name' =>
						[
							'text' => 'Name',
							'sort' => 'name',
							'size' => 13
						]
					]
				],
				'search' =>
				[
					'enabled' => TRUE,
					'columns' => 'name'
				],
				'paging' =>
				[
					'enabled' => TRUE,
					'num_per_page' => 10
				],
				'urls' =>
				[
					'get' => \URL::route('dashboard/work/clients/get-dynamic-table'),
					'add' => \URL::to('dashboard/work/clients/new'),
				]
			]
		);

		$this->initDynamicItem
		(
			[
				'model' => '\App\Models\Client',
				'identifier' =>
				[
					'singular' => 'client',
					'plural' => 'clients'
				],
				'title_column' => 'name',
				'save' =>
				[
					'add' =>
					[
						'success' =>
						[
							'message' => 'Client "%s" added.',
							'redirect' => \URL::route('dashboard/work/clients')
						]
					],
					'edit' =>
					[
						'success' =>
						[
							'message' => 'Client "%s" saved.'
						]
					],
					'item_not_found' =>
					[
						'message' => 'Could not find client with ID "%s".',
						'redirect' => \URL::route('dashboard/work/clients')
					]
				],
				'breadcrumb_items' =>
				[
					'pre' =>
					[
						[
							'text' => 'Work',
							'url' => \URL::route('dashboard/work')
						],
						[
							'text' => 'Clients',
							'url' => \URL::route('dashboard/work/clients')
						]
					]
				],
				'tabs' =>
				[
					'general' =>
					[
						'text' => 'General',
						'save_button' => TRUE,
						'active_toggle' => TRUE
					],
					'featured_projects' =>
					[
						'text' => 'Featured Projects',
						'only_edit' => TRUE
					]
				]
			]
		);
	}

	public function getDynamicTableData(array $options = [])
	{
		$search_query = $options['search_query'];

		$clients = \App\Models\Client::where('name', 'LIKE', '%' . $search_query . '%');

		return $clients;
	}

	public function setDynamicTableColumnData($clients)
	{
		$table_column_data = [];

		foreach ( $clients as $client_index => $client )
		{
			$clients[$client_index]->editURL = $client->getURL(\App\Models\Client::EDIT_URL);
			$clients[$client_index]->deleteURL = $client->getURL(\App\Models\Client::DELETE_URL);
			$clients[$client_index]->table_title = $client->name;

			$table_column_data[$client_index]['logo'] =
			[
				'html' => '<img src="' . $client->getDynamicImageURL('logo', ClientLogoImageController::SIZE_DASHBOARD_DYNAMIC_TABLE) . '" class="ui image mini" alt="">'
			];

			$table_column_data[$client_index]['name'] =
			[
				'html' => '<a href="' . $client->getURL(\App\Models\Client::EDIT_URL) . '">' . e($client->name) . '</a>'
			];
		}

		return
		[
			'table_column_data' => $table_column_data,
			'items' => $clients
		];
	}

	public function dynamicItemPreEdit($client_to_edit)
	{
		$this->dynamic_item_options['save']['edit']['success']['redirect'] = \URL::to($client_to_edit->getURL(\App\Models\Client::EDIT_URL));
	}

	public function clients()
	{
		$this->addBreadcrumbItem('Work', \URL::route('dashboard/work'));
		$this->addBreadcrumbItem('Clients', NULL);

		return $this->display(['Clients', 'Work']);
	}
	
	public function dynamicItemPostAdd($added_client)
	{
		User_Action::add($this->user->id, User_Action::TYPE_ADDED_CLIENT, [ 'client_id' => $added_client->id ]);
	}

	public function dynamicItemPostEdit($changed_client)
	{
		User_Action::add($this->user->id, User_Action::TYPE_CHANGED_CLIENT, [ 'client_id' => $changed_client->id ]);
	}

	public function getFeaturedProjectDialog()
	{
		$client_id = \Input::get('client_id');

		$view = view('dashboard/work/clients/client/tabs/featured_projects/featured_project_dialog');

		// Get projects associated with client (and not already added as a featured project)
		try
		{
			$client = \App\Models\Client::where('id', $client_id)->firstOrFail();

			$available_projects = $client->getAvailableFeaturedProjects();
			$num_available_projects = count($available_projects);

			$view->available_projects = $available_projects;
			$view->num_available_projects = $num_available_projects;

			$this->ajax->addData('num_available_projects', $num_available_projects);
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find client with ID "' . $client_id . '".');
		}

		$this->ajax->addData('html', $view->render());

		return $this->ajax->output();
	}

	public function saveFeaturedProjectDialog()
	{
		$input = \Input::all();
		$client_id = $input['id'];
		$project_id = $input['project_id'];

		// Get next position
		$position = \DB::table('featured_client_projects')
			->where('client_id', $client_id)
			->max('position');

		$position = ($position !== null ? (int)$position + 1 : 0);

		$featured_client_project = new \App\Models\Featured_Client_Project();
		$featured_client_project->client_id = $client_id;
		$featured_client_project->project_id = $project_id;
		$featured_client_project->position = $position;
		$featured_client_project->save();

		\Cache::forget('client_featured_projects_' . $client_id);

		User_Action::add($this->user->id, User_Action::TYPE_ADDED_FEATURED_CLIENT_PROJECT, [ 'featued_client_project_id' => $featured_client_project->id ]);

		$this->ajax->addData('added_featured_project_name', $featured_client_project->project->name);

		return $this->ajax->output();
	}

	public function getFeaturedProjects()
	{
		$client_id = \Input::get('client_id');

		try
		{
			$client = \App\Models\Client::where('id', $client_id)->firstOrFail();
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find client with ID "' . $client_id . '".');
		}

		$featured_projects = $client->getFeaturedProjects();
		$num_featured_projects = count($featured_projects);

		$view = view('dashboard/work/clients/client/tabs/featured_projects/featured_projects');
		$view->featured_projects = $featured_projects;
		$view->num_featured_projects = $num_featured_projects;

		$this->ajax->addData('html', $view->render());
		$this->ajax->addData('num_featured_projects', $num_featured_projects);

		return $this->ajax->output();
	}

	public function saveFeaturedProjects()
	{
		$client_id = \Input::get('client_id');
		$positions = \Input::get('positions');

		try
		{
			foreach ( $positions as $position => $id )
			{
				\DB::update('UPDATE featured_client_projects SET position = ? WHERE project_id = ?', [ $position, $id ]);
			}

			\Cache::forget('client_featured_projects_' . $client_id);
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find client with ID "' . $client_id . '".');
		}

		return $this->ajax->output();
	}
}