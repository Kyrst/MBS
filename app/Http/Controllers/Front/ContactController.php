<?php namespace App\Http\Controllers\Front;

class ContactController extends \App\Http\Controllers\FrontController
{
	public function contact()
	{
		return $this->display('Contact Studio City', FALSE);
	}
}