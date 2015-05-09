<?php namespace App\Http\Controllers;

class FrontController extends ApplicationController
{
	public $layout = 'front';

	public function afterLayoutInit()
	{
		parent::afterLayoutInit();
	}
}