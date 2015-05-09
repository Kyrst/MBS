<?php namespace App\Helpers\Core;

class UI
{
	const MESSAGE_SESSION_NAME = 'core_message';

	const MESSAGE_TYPE_DEFAULT = 1;
	const MESSAGE_TYPE_SUCCESS = 2;
	const MESSAGE_TYPE_INFO = 3;
	const MESSAGE_TYPE_WARNING = 4;
	const MESSAGE_TYPE_ERROR = 5;

	private $message =
	[
		'pre_loaded' => NULL,
		'user_loaded' => NULL
	];

	public function setMessage($text, $type = self::MESSAGE_TYPE_DEFAULT)
	{
		$this->message['user_loaded'] =
		[
			'text' => $text,
			'type' => $type
		];

		$this->saveMessage();
	}

	public function showMessage($text)
	{
		$this->setMessage($text, self::MESSAGE_TYPE_DEFAULT);
	}

	public function showSuccess($text)
	{
		$this->setMessage($text, self::MESSAGE_TYPE_SUCCESS);
	}

	public function showInfo($text)
	{
		$this->setMessage($text, self::MESSAGE_TYPE_INFO);
	}

	public function showWarning($text)
	{
		$this->setMessage($text, self::MESSAGE_TYPE_WARNING);
	}

	public function showError($text)
	{
		$this->setMessage($text, self::MESSAGE_TYPE_ERROR);
	}

	public function deleteMessage()
	{
		\Session::forget(self::MESSAGE_SESSION_NAME);
	}

	private function saveMessage()
	{
		\Session::set(self::MESSAGE_SESSION_NAME, $this->message['user_loaded']);
	}

	public function getMessage()
	{
		return \Session::get(self::MESSAGE_SESSION_NAME);
	}

	public function haveMessage()
	{
		return \Session::has(self::MESSAGE_SESSION_NAME);
	}

	public function output()
	{
		$this->deleteSession();

		return json_encode($this->message['pre_loaded']);
	}
}