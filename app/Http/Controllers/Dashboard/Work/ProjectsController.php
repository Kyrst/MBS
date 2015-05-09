<?php namespace App\Http\Controllers\Dashboard\Work;

use App\Http\Controllers\Image\ProjectThumbnailImageController;
use App\Models\Image_Generation_Queue;
use App\Models\User_Action;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProjectsController extends \App\Http\Controllers\Dashboard\WorkController
{
	use \App\Helpers\Core\DynamicTable, \App\Helpers\Core\DynamicItem;

	function __construct()
	{
		parent::__construct();

		$this->initDynamicTable
		(
			[
				'route' => \URL::route('dashboard/work/projects'),
				'model' => '\App\Models\Project',
				'identifier' =>
				[
					'singular' => 'project',
					'plural' => 'projects'
				],
				'default_sort_column' => 'name',
				'default_sort_order' => 'asc',
				'table' =>
				[
					'columns' =>
					[
						'thumbnail_image' =>
						[
							'text' => '',
							'size' => 1,
						],
						'name' =>
						[
							'text' => 'Name',
							'sort' => 'name',
							'size' => 5
						],
						'client' =>
						[
							'text' => 'Client',
							'size' => 4
						],
						'services' =>
						[
							'text' => 'Services',
							'size' => 4
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
					'get' => \URL::route('dashboard/work/projects/get-dynamic-table'),
					'add' => \URL::to('dashboard/work/projects/new'),
				]
			]
		);

		$this->initDynamicItem
		(
			[
				'model' => '\App\Models\Project',
				'identifier' =>
				[
					'singular' => 'project',
					'plural' => 'projects'
				],
				'title_column' => 'name',
				'save' =>
				[
					'add' =>
					[
						'success' =>
						[
							'message' => 'Project "%s" added.',
							'redirect' => \URL::route('dashboard/work/projects')
						]
					],
					'edit' =>
					[
						'success' =>
						[
							'message' => 'Project "%s" saved.'
						]
					],
					'item_not_found' =>
					[
						'message' => 'Could not find project with ID "%s".',
						'redirect' => \URL::route('dashboard/work/projects')
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
							'text' => 'Projects',
							'url' => \URL::route('dashboard/work/projects')
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
					'promos' =>
					[
						'text' => 'Promos',
						'only_edit' => TRUE
					],
					'related_work' =>
					[
						'text' => 'Related Work',
						'only_edit' => TRUE
					]
				]
			]
		);
	}

	public function getDynamicTableData(array $options = [])
	{
		$search_query = $options['search_query'];

		$projects = \App\Models\Project::where('name', 'LIKE', '%' . $search_query . '%');

		return $projects;
	}

	public function setDynamicTableColumnData($projects)
	{
		$table_column_data = [];

		foreach ( $projects as $project_index => $project )
		{
			$projects[$project_index]->editURL = $project->getURL(\App\Models\Project::EDIT_URL);
			$projects[$project_index]->deleteURL = $project->getURL(\App\Models\Project::DELETE_URL);
			$projects[$project_index]->table_title = $project->name;

			$table_column_data[$project_index]['thumbnail_image'] =
			[
				'html' => '<img src="' . $project->getDynamicImageURL('thumbnail', ProjectThumbnailImageController::SIZE_DASHBOARD_DYNAMIC_TABLE) . '" alt="" class="ui image mini">'
			];

			$table_column_data[$project_index]['name'] =
			[
				'html' => '<a href="' . $project->getURL(\App\Models\Project::EDIT_URL) . '">' . e($project->name) . '</a>'
			];

			$table_column_data[$project_index]['client'] =
			[
				'html' => '<a href="' . $project->client->getURL(\App\Models\Client::EDIT_URL) . '">' . e($project->client->name) . '</a>'
			];

			$services_html = '';

			$project_services = $project->services;
			$num_services = $project_services->count();

			if ( $num_services > 0 )
			{
				foreach ( $project_services as $service_index => $project_service )
				{
					$services_html .= '<a href="' . $project_service->service->getURL(\App\Models\Service::EDIT_URL) . '">' . e($project_service->service->name) . '</a>' . ($service_index < ($num_services - 1) ? ', ' : '');
				}
			}
			else
			{
				$services_html .= '-';
			}

			$table_column_data[$project_index]['services'] =
			[
				'html' => $services_html
			];
		}

		return
		[
			'table_column_data' => $table_column_data,
			'items' => $projects
		];
	}

	public function getDynamicItemTabHTML($view, $project_to_edit, $tab_id)
	{
		// Clients
		$clients = \App\Models\Client::orderBy('name')->get();

		$view->clients = $clients;
		$view->num_clients = $clients->count();

		// Services
		$services = \App\Models\Service::orderBy('name')->get();

		$view->services = $services;
		$view->num_services = $services->count();

		if ( $project_to_edit !== NULL )
		{
			$project_to_edit_services = $project_to_edit->services()->select(['service_id'])->get()->toArray();
			$project_to_edit_service_ids = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($project_to_edit_services)),false);

			$view->project_to_edit_service_ids = $project_to_edit_service_ids;
		}

		return $view;
	}

	public function dynamicItemPreEdit($project_to_edit)
	{
		$this->dynamic_item_options['save']['edit']['success']['redirect'] = \URL::to($project_to_edit->getURL(\App\Models\Project::EDIT_URL));
	}

	public function projects()
	{
		$this->addBreadcrumbItem('Work', \URL::route('dashboard/work'));
		$this->addBreadcrumbItem('Projects');

		return $this->display(['Projects', 'Work']);
	}

	public function dynamicItemPostAdd($added_project)
	{
		User_Action::add($this->user->id, User_Action::TYPE_ADDED_PROJECT, [ 'project_id' => $added_project->id ]);

		\Cache::forget('client_featured_projects_' . $added_project->client_id);
	}

	public function dynamicItemPostEdit($changed_project)
	{
		User_Action::add($this->user->id, User_Action::TYPE_CHANGED_PROJECT, [ 'project_id' => $changed_project->id ]);

		\Cache::forget('client_featured_projects_' . $changed_project->client_id);

		// Clear featured projects cache
		$featured_client_projects = \App\Models\Featured_Client_Project::where('project_id')
			->get();

		foreach ( $featured_client_projects as $featured_client_project )
		{
			\Cache::forget('client_featured_projects_' . $featured_client_project->client_id);
		}
	}

	public function getPromoDialog()
	{
		$promo_id_to_edit = \Input::get('promo_id');

		$promo_to_edit = NULL;

		if ( $promo_id_to_edit !== NULL )
		{
			try
			{
				$promo_to_edit = \App\Models\Project_Promo::where('id', $promo_id_to_edit)->firstOrFail();
			}
			catch ( ModelNotFoundException $e )
			{
			}
		}

		$view = view('dashboard/work/projects/project/tabs/promos/promo_dialog');
		$view->promo_to_edit = $promo_to_edit;

		$this->ajax->addData('html', $view->render());

		return $this->ajax->output();
	}

	public function getPromos($project_id)
	{
		try
		{
			$project = \App\Models\Project::where('id', $project_id)->firstOrFail();
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find project with ID "' . $project_id . '".');
		}

		$promos = $project->promos;
		$num_promos = count($promos);

		$view = view('dashboard/work/projects/project/tabs/promos/promos');
		$view->promos = $promos;
		$view->num_promos = $num_promos;

		$this->ajax->addData('html', $view->render());
		$this->ajax->addData('num_promos', $num_promos);

		return $this->ajax->output();
	}

	public function savePromoDialog()
	{
		$input = \Input::all();
		$project_id = $input['id'];
		$promo_id = $input['promo_id'];

		try
		{
			$project = \App\Models\Project::where('id', $project_id)->firstOrFail();
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find project with ID "' . $project_id . '".');
		}

		if ( $promo_id !== '' )
		{
			try
			{
				$promo = \App\Models\Project_Promo::where('id', $promo_id)->firstOrFail();

				$promo->edit
				(
					[
						'vimeo_id' => $input['vimeo_id'],
						'title' => $input['title']
					]
				);

				User_Action::add($this->user->id, User_Action::TYPE_CHANGED_PROJECT_PROMO, [ 'project_promo_id' => $promo->id ]);
			}
			catch ( ModelNotFoundException $e )
			{
				return $this->ajax->outputWithError('Could not find promo with ID "' . $promo_id . '".');
			}
		}
		else
		{
			$added_project_promo = $project->addPromo
			(
				[
					'vimeo_id' => $input['vimeo_id'],
					'title' => $input['title']
				]
			);

			User_Action::add($this->user->id, User_Action::TYPE_ADDED_PROJECT_PROMO, [ 'project_promo_id' => $added_project_promo->id ]);

			$this->ajax->addData('project_promo_id', $added_project_promo->id);
		}

		return $this->ajax->output();
	}

	public function uploadPromoDialogImage()
	{
		$project_id = $_SERVER['HTTP_PROJECT_ID'];
		$project_promo_id = $_SERVER['HTTP_PROMO_ID'];
		$mime = $_SERVER['HTTP_MIME'];

		try
		{
			$project_promo = \App\Models\Project_Promo::where('id', $project_promo_id)->firstOrFail();

			$upload_dir = $project_promo->getImageDirectory($project_id);

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

			Image_Generation_Queue::add(Image_Generation_Queue::TYPE_PROJECT_PROMO, $project_promo_id);

			$project_promo->setImage
			(
				[
					'file_extension' => $file_extension,
					'mime' => $mime,
					'index' => 0,
					'processing' => 'yes'
				]
			);
		}
		catch ( ModelNotFoundException $e )
		{
		}

		return \Response::json();
	}

	public function deletePromo()
	{
		$id = \Input::get('id');

		try
		{
			$promo = \App\Models\Project_Promo::where('id', $id)->firstOrFail();
			$promo->delete();
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find promo with ID "' . $id . '".');
		}

		return $this->ajax->output();
	}

	public function getRelatedWork($project_id)
	{
		try
		{
			$project = \App\Models\Project::where('id', $project_id)->firstOrFail();
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find project with ID "' . $project_id . '".');
		}

		$related_work_items = $project->relatedWorkItems;
		$num_related_work_items = count($related_work_items);

		$view = view('dashboard/work/projects/project/tabs/related_work/related_work');
		$view->related_work_items = $related_work_items;
		$view->num_related_work_items = $num_related_work_items;

		$this->ajax->addData('html', $view->render());
		$this->ajax->addData('num_related_work_items', $num_related_work_items);

		return $this->ajax->output();
	}

	public function getRelatedWorkDialog()
	{
		$related_work_item_id = \Input::get('related_work_id');

		$related_work_item_to_edit = NULL;

		if ( $related_work_item_id !== NULL )
		{
			try
			{
				$related_work_item_to_edit = \App\Models\Related_Work::where('id', $related_work_item_id)->firstOrFail();
			}
			catch ( ModelNotFoundException $e )
			{
			}
		}

		$view = view('dashboard/work/projects/project/tabs/related_work/related_work_dialog');
		$view->related_work_item_to_edit = $related_work_item_to_edit;
		$view->services = \App\Models\Service::orderBy('name')->get();

		$this->ajax->addData('html', $view->render());

		return $this->ajax->output();
	}

	public function saveRelatedWorkDialog()
	{
		$input = \Input::all();
		$project_id = $input['id'];
		$related_work_item_id = $input['related_work_item_id'];

		try
		{
			$project = \App\Models\Project::where('id', $project_id)->firstOrFail();
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find project with ID "' . $project_id . '".');
		}

		if ( $related_work_item_id !== '' )
		{
			try
			{
				$related_work_item = \App\Models\Related_Work::where('id', $related_work_item_id)->firstOrFail();

				$related_work_item->edit
				(
					[
						'vimeo_id' => $input['vimeo_id'],
						'title' => $input['title'],
						'service_id' => $input['service_id']
					]
				);

				User_Action::add($this->user->id, User_Action::TYPE_CHANGED_RELATED_WORK, [ 'related_work_item_id' => $related_work_item->id ]);
			}
			catch ( ModelNotFoundException $e )
			{
				return $this->ajax->outputWithError('Could not find related work with ID "' . $related_work_item_id . '".');
			}
		}
		else
		{
			$added_related_work_item = $project->addRelatedWork
			(
				[
					'vimeo_id' => $input['vimeo_id'],
					'title' => $input['title'],
					'service_id' => $input['service_id']
				]
			);

			User_Action::add($this->user->id, User_Action::TYPE_ADDED_RELATED_WORK, [ 'related_work_item_id' => $added_related_work_item->id ]);

			$this->ajax->addData('related_work_item_id', $added_related_work_item->id);
		}

		return $this->ajax->output();
	}

	public function uploadRelatedWorkDialogThumbnailImage()
	{
		$project_id = $_SERVER['HTTP_PROJECT_ID'];
		$related_work_item_id = $_SERVER['HTTP_RELATED_WORK_ID'];
		$mime = $_SERVER['HTTP_MIME'];

		try
		{
			$related_work_item = \App\Models\Related_Work::where('id', $related_work_item_id)->firstOrFail();

			$upload_dir = $related_work_item->getThumbnailImageDirectory($project_id);

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

			Image_Generation_Queue::add(Image_Generation_Queue::TYPE_RELATED_WORK, $related_work_item_id);

			$related_work_item->setThumbnailImage
			(
				[
					'file_extension' => $file_extension,
					'mime' => $mime,
					'index' => 0,
					'processing' => 'yes'
				]
			);
		}
		catch ( ModelNotFoundException $e )
		{
		}

		return \Response::json();
	}
}