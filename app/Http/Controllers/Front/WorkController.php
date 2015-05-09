<?php namespace App\Http\Controllers\Front;

class WorkController extends \App\Http\Controllers\FrontController
{
	public function work()
	{
		$projects = \App\Models\Project::active()->orderBy('name')->get();
		$services = \App\Models\Service::active()->orderBy('name')->get();
		$clients = \App\Models\Client::active()->orderBy('name')->get();

		$this->assign('projects', $projects);
		$this->assign('services', $services);
		$this->assign('clients', $clients);

		return $this->display('Work');
	}
}