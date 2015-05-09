<?php namespace App\Http\Controllers\Front;

class AboutController extends \App\Http\Controllers\FrontController
{
	public function about()
	{
		return $this->display('About Studio City', FALSE);
	}
}