<?php namespace App\Http\Controllers\Front;

class ClientsController extends \App\Http\Controllers\FrontController
{
	public function clients()
	{
		$clients = \App\Models\Client::active()->orderBy('name')->get();

		$this->assign('clients', $clients);
		
		return $this->display('Clients');
	}
}
