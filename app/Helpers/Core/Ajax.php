<?php namespace App\Helpers\Core;

class Ajax
{
	private $ui;

	private $data =
	[
		'data' => [],
		'redirect' => NULL,
		'message' => NULL,
		'error' => NULL
	];

	private $data_keys = [];

	function __construct(UI $ui)
	{
		$this->ui = $ui;

		$this->data_keys = array_keys($this->data);
	}

	public function addData($key, $value)
	{
		if ( in_array($key, $this->data_keys) )
		{
			throw new \Exception('Can\'t add data with key "' . $key . '" because it\'s a restricted keyword.');
		}

		$this->data['data'][$key] = $value;
	}

	public function setError($error_message)
	{
		$this->data['error'] = $error_message;
	}

	public function redirect($url, $delay_in_ms = 0)
	{
		$this->data['redirect'] =
		[
			'url' => $url,
			'delay' => $delay_in_ms
		];

		return $this->output();
	}

	public function showSuccess($text)
	{
		$this->data['message'] =
		[
			'text' => $text,
			'type' => UI::MESSAGE_TYPE_SUCCESS
		];
	}

	public function showInfo($text)
	{
		$this->data['message'] =
		[
			'text' => $text,
			'type' => UI::MESSAGE_TYPE_INFO
		];
	}

	public function showWarning($text)
	{
		$this->data['message'] =
		[
			'text' => $text,
			'type' => UI::MESSAGE_TYPE_WARNING
		];
	}

	public function showError($text)
	{
		$this->data['message'] =
		[
			'text' => $text,
			'type' => UI::MESSAGE_TYPE_ERROR
		];
	}

	public function output($cookie = NULL)
	{
		ob_start();

		if ( $this->data['redirect'] !== NULL && $this->data['message'] !== NULL )
		{
			$this->ui->setMessage($this->data['message']['text'], $this->data['message']['type']);

			$this->data['message'] = NULL;
		}

		$response = \Response::json($this->data);

		if ( $cookie !== NULL )
		{
			$response->withCookie($cookie);
		}

		return $response;
	}

	public function outputWithError($error)
	{
		$this->setError($error);

		return $this->output();
	}
}