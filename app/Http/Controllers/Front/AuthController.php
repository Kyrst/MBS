<?php namespace App\Http\Controllers\Front;

class AuthController extends \App\Http\Controllers\FrontController
{
	public function signIn()
	{
		return $this->display('Sign In');
	}

	public function signInPost()
	{
		$input = \Input::all();

		$email = trim($input['email']);
		$password = trim($input['password']);

		if ( !\Auth::attempt(['email' => $email, 'password' => $password], TRUE) )
		{
			return $this->ajax->outputWithError('Invalid email or password.');
		}

		return $this->ajax->output();
	}

	public function signOut()
	{
		if ( $this->user !== NULL )
		{
			\Auth::logout();
		}

		return \Redirect::route('home');
	}
}