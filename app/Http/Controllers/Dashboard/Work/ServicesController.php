<?php namespace App\Http\Controllers\Dashboard\Work;

use App\Models\User_Action;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ServicesController extends \App\Http\Controllers\Dashboard\WorkController
{
	use \App\Helpers\Core\DynamicTable, \App\Helpers\Core\DynamicItem;

	function __construct()
	{
		parent::__construct();

		$this->initDynamicTable
		(
			[
				'route' => \URL::route('dashboard/work/services'),
				'model' => '\App\Models\Service',
				'identifier' =>
				[
					'singular' => 'service',
					'plural' => 'services'
				],
				'default_sort_column' => 'name',
				'default_sort_order' => 'asc',
				'table' =>
				[
					'columns' =>
					[
						'name' =>
						[
							'text' => 'Name',
							'sort' => 'name',
							'size' => 14
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
					'get' => \URL::route('dashboard/work/services/get-dynamic-table'),
					'add' => \URL::to('dashboard/work/services/new'),
				]
			]
		);

		$this->initDynamicItem
		(
			[
				'model' => '\App\Models\Service',
				'identifier' =>
				[
					'singular' => 'service',
					'plural' => 'services'
				],
				'title_column' => 'name',
				'save' =>
				[
					'add' =>
					[
						'success' =>
						[
							'message' => 'Service "%s" added.',
							'redirect' => \URL::route('dashboard/work/services')
						]
					],
					'edit' =>
					[
						'success' =>
						[
							'message' => 'Service "%s" saved.'
						]
					],
					'item_not_found' =>
					[
						'message' => 'Could not find service with ID "%s".',
						'redirect' => \URL::route('dashboard/work/services')
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
							'text' => 'Services',
							'url' => \URL::route('dashboard/work/services')
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

		$services = \App\Models\Service::where('name', 'LIKE', '%' . $search_query . '%');

		return $services;
	}

	public function setDynamicTableColumnData($services)
	{
		$table_column_data = [];

		foreach ( $services as $service_index => $service )
		{
			$services[$service_index]->editURL = $service->getURL(\App\Models\Service::EDIT_URL);
			$services[$service_index]->deleteURL = $service->getURL(\App\Models\Service::DELETE_URL);
			$services[$service_index]->table_title = $service->name;

			$table_column_data[$service_index]['name'] =
			[
				'html' => '<a href="' . $service->getURL(\App\Models\Service::EDIT_URL) . '">' . e($service->name) . '</a>'
			];
		}

		return
		[
			'table_column_data' => $table_column_data,
			'items' => $services
		];
	}

	public function dynamicItemPreEdit($service_to_edit)
	{
		$this->dynamic_item_options['save']['edit']['success']['redirect'] = \URL::to($service_to_edit->getURL(\App\Models\Service::EDIT_URL));
	}

	public function services()
	{
		$this->addBreadcrumbItem('Work', \URL::route('dashboard/work'));
		$this->addBreadcrumbItem('Services', NULL);

		return $this->display(['Services', 'Work']);
	}

	public function dynamicItemPostAdd($added_service)
	{
		User_Action::add($this->user->id, User_Action::TYPE_ADDED_SERVICE, [ 'service_id' => $added_service->id ]);
	}

	public function dynamicItemPostEdit($changed_service)
	{
		User_Action::add($this->user->id, User_Action::TYPE_CHANGED_SERVICE, [ 'service_id' => $changed_service->id ]);
	}

	public function getFeaturedProjectDialog()
	{
		$service_id = \Input::get('service_id');

		$view = view('dashboard/work/services/service/tabs/featured_projects/featured_project_dialog');

		// Get projects associated with service (and not already added as a featured project)
		try
		{
			$service = \App\Models\Service::where('id', $service_id)->firstOrFail();

			$available_projects = $service->getAvailableFeaturedProjects();
			$num_available_projects = count($available_projects);

			$view->available_projects = $available_projects;
			$view->num_available_projects = $num_available_projects;

			$this->ajax->addData('num_available_projects', $num_available_projects);
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find service with ID "' . $service_id . '".');
		}

		$this->ajax->addData('html', $view->render());

		return $this->ajax->output();
	}

	public function saveFeaturedProjectDialog()
	{
		$input = \Input::all();
		$service_id = $input['id'];
		$project_id = $input['project_id'];

		try
		{
			$project_service = \App\Models\Project_Service::where('project_id', $project_id)
				->where('service_id', $service_id)
				->firstOrFail();

			// Get next position
			$position = \DB::table('project_services')
				->where('service_id', $service_id)
				->where('featured', 'yes')
				->max('position');

			$position = ($position !== null ? (int)$position + 1 : 0);

			$project_service->featured = 'yes';
			$project_service->position = $position;
			$project_service->save();

			User_Action::add($this->user->id, User_Action::TYPE_ADDED_FEATURED_SERVICE_PROJECT, [ 'project_service_id' => $project_service->id ]);

			$this->ajax->addData('added_featured_project_name', $project_service->project->name);
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not save featured project.');
		}

		return $this->ajax->output();
	}

	public function getFeaturedProjects()
	{
		$service_id = \Input::get('service_id');

		try
		{
			$service = \App\Models\Service::where('id', $service_id)->firstOrFail();
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find service with ID "' . $service_id . '".');
		}

		$featured_projects = $service->getFeaturedProjects();
		$num_featured_projects = count($featured_projects);

		$view = view('dashboard/work/services/service/tabs/featured_projects/featured_projects');
		$view->featured_projects = $featured_projects;
		$view->num_featured_projects = $num_featured_projects;

		$this->ajax->addData('html', $view->render());
		$this->ajax->addData('num_featured_projects', $num_featured_projects);

		return $this->ajax->output();
	}

	public function saveFeaturedProjects()
	{
		$service_id = \Input::get('service_id');
		$positions = \Input::get('positions');

		try
		{
			foreach ( $positions as $position => $project_service_id )
			{
				\DB::update('UPDATE project_services SET position = ? WHERE id = ?', [ $position, $project_service_id ]);
			}
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find service with ID "' . $service_id . '".');
		}

		return $this->ajax->output();
	}
}