<?php namespace App\Http\Controllers\Front;

class CareersController extends \App\Http\Controllers\FrontController
{
	public function careers()
	{
		$form_view = view('front/careers/form');
		$form_view->csrf_token = csrf_token();
		$this->assign('form_html', $form_view->render());

		return $this->display('Careers');
	}

	public function apply()
	{
		$input = \Input::all();

		$first_name = trim($input['firstName']);
		$last_name = trim($input['lastName']);
		$email = trim($input['email']);

		$resume = (isset($input['resume']) ? $input['resume'] : null);
		$cover_letter = (isset($input['coverLetter']) ? $input['coverLetter'] : null);

		$email_data =
		[
			'first_name' => $input['firstName'],
			'last_name' => $input['lastName'],
			'email' => $input['email'],
			'cell_phone' => $input['phone'],
			'alt_phone' => (isset($input['phoneAlt']) ? $input['phoneAlt'] : null),
			'website_url' => $input['website'],
			'referer' => $input['referer'],
			'time' => date('n/j/Y g:i A'),
			'ip_address' => \Request::getClientIp()
		];

		\Mail::send(['emails.html.application', 'emails.plain.application'], $email_data, function($message) use ($first_name, $last_name, $email, $resume, $cover_letter)
		{
			$message->from($email, $first_name . ' ' . $last_name);
			$message->to(\Config::get('custom.CAREER_APPLICATION_TO_EMAIL'), 'Studio City')->subject('Career Application');

			if ( $resume !== null )
			{
				$message->attach($resume->getRealPath(), ['as' => $resume->getClientOriginalName(), 'mime' => $resume->getClientMimeType()]);
			}

			if ( $cover_letter !== null )
			{
				$message->attach($cover_letter->getRealPath(), ['as' => $cover_letter->getClientOriginalName(), 'mime' => $cover_letter->getClientMimeType()]);
			}
		});

		$this->ui->showSuccess('Thank you for your application!');

		return redirect('careers');
	}
}