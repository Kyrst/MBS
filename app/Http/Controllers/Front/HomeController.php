<?php namespace App\Http\Controllers\Front;

class HomeController extends \App\Http\Controllers\FrontController
{
	public function home()
	{
		return $this->display(\Config::get('custom.PROJECT_NAME'), FALSE);
	}
}