<?php namespace App\Http\Controllers\Dashboard;

class WorkController extends \App\Http\Controllers\DashboardController
{
	public function work()
	{
		$this->addBreadcrumbItem('Work', NULL);

		return $this->display('Work');
	}
}