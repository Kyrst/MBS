<?php namespace App\Http\Controllers\Dashboard;

class HomePageController extends \App\Http\Controllers\DashboardController
{
	public function home()
	{
		$this->addBreadcrumbItem('Home Page');

		return $this->display(['Home Page', 'Dashboard']);
	}
}