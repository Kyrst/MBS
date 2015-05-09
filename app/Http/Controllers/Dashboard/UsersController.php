<?php namespace App\Http\Controllers\Dashboard;

use App\Models\User_Action;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UsersController extends \App\Http\Controllers\DashboardController
{
	use \App\Helpers\Core\DynamicTable, \App\Helpers\Core\DynamicItem;

	function __construct()
	{
		parent::__construct();

		$this->initDynamicTable
		(
			[
				'route' => \URL::route('dashboard/users'),
				'model' => '\App\Models\User',
				'identifier' =>
				[
					'singular' => 'user',
					'plural' => 'users'
				],
				'default_sort_column' => 'name',
				'default_sort_order' => 'asc',
				'table' =>
				[
					'columns' =>
					[
						'email' =>
						[
							'text' => 'Email',
							'sort' => 'email',
							'size' => 4
						],
						'name' =>
						[
							'text' => 'Name',
							'sort' => \DB::raw('CONCAT(users.first_name, " ", users.last_name)'),
							'size' => 4
						],
						'last_action' =>
						[
							'text' => 'Last Action',
							'size' => 6
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
					'get' => \URL::route('dashboard/users/get-dynamic-table'),
					'add' => \URL::to('dashboard/users/new'),
				]
			]
		);

		$this->initDynamicItem
		(
			[
				'model' => '\App\Models\User',
				'identifier' =>
				[
					'singular' => 'user',
					'plural' => 'users'
				],
				'title_column' => 'email',
				'save' =>
				[
					'add' =>
					[
						'success' =>
						[
							'message' => 'User "%s" added.',
							'redirect' => \URL::route('dashboard/users')
						]
					],
					'edit' =>
					[
						'success' =>
						[
							'message' => 'User "%s" saved.'
						]
					],
					'item_not_found' =>
					[
						'message' => 'Could not find user with ID "%s".',
						'redirect' => \URL::route('dashboard/users')
					]
				],
				'breadcrumb_items' =>
				[
					'pre' =>
					[
						[
							'text' => 'Users',
							'url' => \URL::route('dashboard/users')
						]
					]
				]
			]
		);
	}

	public function getDynamicTableData(array $options = [])
	{
		$search_query = $options['search_query'];

		$users = \App\Models\User::where(\DB::raw('CONCAT(users.first_name, " ", users.last_name)'), 'LIKE', '%' . $search_query . '%')
			->orWhere('users.email', 'LIKE', '%' . $search_query . '%');

		return $users;
	}

	public function setDynamicTableColumnData($users)
	{
		$table_column_data = [];

		foreach ( $users as $user_index => $user )
		{
			$users[$user_index]->editURL = $user->getURL(\App\Models\User::EDIT_URL);
			$users[$user_index]->deleteURL = $user->getURL(\App\Models\User::DELETE_URL);
			$users[$user_index]->table_title = $user->name;

			$table_column_data[$user_index]['name'] =
			[
				'html' => '<a href="' . $user->getURL(\App\Models\User::EDIT_URL) . '">' . e($user->getName()) . '</a>'
			];

			$table_column_data[$user_index]['email'] =
			[
				'html' => $user->email
			];

			$last_action = $user->getLastAction();

			$table_column_data[$user_index]['last_action'] =
			[
				'html' => ($last_action !== null ? $last_action->format(FALSE) : '-')
			];
		}

		return
		[
			'table_column_data' => $table_column_data,
			'items' => $users
		];
	}

	public function users()
	{
		$this->addBreadcrumbItem('Users', null);

		return $this->display('Users');
	}

	public function dynamicItemPostAdd($added_user)
	{
		User_Action::add($this->user->id, User_Action::TYPE_ADDED_USER, [ 'user_id' => $added_user->id ]);
	}

	public function dynamicItemPostEdit($changed_user)
	{
		User_Action::add($this->user->id, User_Action::TYPE_CHANGED_USER, [ 'user_id' => $changed_user->id ]);
	}

	public function userProfile($id)
	{
		try
		{
			$profile_user = \App\Models\User::where('id', $id)->firstOrFail();
		}
		catch ( ModelNotFoundException $e )
		{
			$this->ui->showWarning('Could not find user with ID ' . $id . '.');

			return \Redirect::route('dashboard/users');
		}

		$this->assign('profile_user', $profile_user);

		$this->addBreadcrumbItem('Users', \URL::route('dashboard/users'));
		$this->addBreadcrumbItem(e($profile_user->getName()), NULL);

		return $this->display([$profile_user->getName(), 'Users']);
	}
}