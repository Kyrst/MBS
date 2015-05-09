<?php namespace App\Http\Controllers\Front\Work;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProjectsController extends \App\Http\Controllers\Front\WorkController
{
	public function project($slug)
	{
		try
		{
			$project = \App\Models\Project::where('slug', $slug)
				->active()
				->firstOrFail();
		}
		catch ( ModelNotFoundException $e )
		{
			return \App::abort(404);
		}

		$project_services = $project->services;
		$project_promos = $project->promos;

		$this->assign('project', $project);
		$this->assign('project_services', $project_services);
		$this->assign('project_promos', $project_promos);

		return $this->display([ $project->name, 'Work' ]);
	}
}