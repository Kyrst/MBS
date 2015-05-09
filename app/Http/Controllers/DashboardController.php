<?php namespace App\Http\Controllers;

class DashboardController extends ApplicationController
{
	public $layout = 'dashboard';

	private $breadcrumb_items = [];

	function __construct()
	{
		$this->beforeFilter('@filterRequests', ['on' => 'get']);
	}

	public function filterRequests($route, $request)
	{
		$this->user = \Auth::user();

		if ( $this->user === NULL && $route->getActionName() !== 'App\Http\Controllers\Dashboard\AuthController@signIn' )
		{
			return \Redirect::route('sign-in');
		}

		$this->assign('volume', ($this->user !== null ? $this->user->getVolume() : 100), [CoreController::SECTION_LAYOUT, CoreController::SECTION_JS]);
	}

	public function afterLayoutInit()
	{
		$this->initMenu();
		$this->initBreadcrumb();

		parent::afterLayoutInit();
	}

	public function beforeDisplay()
	{
		$breadcrumb_view = view('layouts/partials/dashboard/breadcrumb');
		$breadcrumb_view->breadcrumb_items = $this->breadcrumb_items;
		$breadcrumb_view->num_breadcrumb_items = count($this->breadcrumb_items);

		$this->assign('breadcrumb', $breadcrumb_view->render(), CoreController::SECTION_LAYOUT);

		parent::beforeDisplay();
	}

	private function initMenu()
	{
		$menu_items =
		[
			[
				'text' => 'Dashboard',
				'icon' => 'dashboard',
				'link' => \URL::route('dashboard'),
				'pages' => ['dashboard/home/home']
			]
		];

		$this->assign('menu_items', $menu_items, CoreController::SECTION_LAYOUT);
	}

	private function initBreadcrumb()
	{
		if ( $this->current_page !== 'dashboard/home/home' )
		{
			$this->addBreadcrumbItem('Dashboard', \URL::route('dashboard'));
		}
	}

	protected function addBreadcrumbItem($text, $link = NULL)
	{
		$this->breadcrumb_items[] =
		[
			'text' => $text,
			'link' => $link
		];
	}

	public function saveVolume()
	{
		$volume = \Input::get('volume', 100);

		$this->user->volume = $volume;
		$this->user->save();

		return $this->ajax->output();
	}
}