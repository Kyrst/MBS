<?php namespace App\Http\Controllers;

class ApplicationController extends CoreController
{
	protected $user;

	private $_current_controller;
	private $_current_controller_parts;
	private $_current_action;

	public function callAction($method, $parameters)
	{
		parent::initLayout($method, $parameters);

		$this->user = \Auth::user();
		$this->assign('user', $this->user, [CoreController::SECTION_LAYOUT, CoreController::SECTION_CONTENT]);

		if ( !$this->is_ajax )
		{
			$this->_current_controller = $this->getCurrentController();
			$this->_current_controller_parts = explode('/', $this->_current_controller);
			$this->_current_action = $this->getCurrentAction();

			$assets = json_decode(file_get_contents(base_path() . '/assets.json'), TRUE);

			foreach ( $assets['libraries'] as $library_name => $library_data )
			{
				$css = (isset($library_data['css']) ? $library_data['css'] : NULL);
				$js = (isset($library_data['js']) ? $library_data['js'] : NULL);

				$this->addLibrary($library_name, $css, $js);
			}

			$this->loadPages($assets['pages']);
		}

		return parent::callAction($method, $parameters);
	}

	private function loadPages($pages)
	{
		foreach ( $pages as $controller => $controller_data )
		{
			if ( (!in_array($controller, $this->_current_controller_parts) && $controller !== $this->_current_controller) && $controller !== 'all' )
			{
				continue;
			}

			if ( isset($controller_data['libraries']) )
			{
				if ( is_array($controller_data['libraries']) )
				{
					foreach ( $controller_data['libraries'] as $library_name )
					{
						$this->loadLibrary($library_name);
					}
				}
				else
				{
					$this->loadLibrary($controller_data['libraries']);
				}
			}

			if ( isset($controller_data['assets']) )
			{
				if ( isset($controller_data['assets']['css']) )
				{
					if ( is_array($controller_data['assets']['css']) )
					{
						foreach ( $controller_data['assets']['css'] as $css_file )
						{
							$this->loadCSS($css_file);
						}
					}
					else
					{
						$this->loadCSS($controller_data['assets']['css']);
					}
				}

				if ( isset($controller_data['assets']['js']) )
				{
					if ( is_array($controller_data['assets']['js']) )
					{
						foreach ( $controller_data['assets']['js'] as $js_file )
						{
							$this->loadJS($js_file);
						}
					}
					else
					{
						$this->loadJS($controller_data['assets']['js']);
					}
				}
			}

			if ( isset($controller_data['actions']) )
			{
				foreach ( $controller_data['actions'] as $action => $action_data )
				{
					if ( $action !== $this->_current_action )
					{
						continue;
					}

					if ( is_array($action_data['libraries']) )
					{
						foreach ( $action_data['libraries'] as $library_name )
						{
							$this->loadLibrary($library_name);
						}
					}
					else
					{
						$this->loadLibrary($action_data['libraries']);
					}

					if ( isset($action_data['assets']) )
					{
						if ( is_array($action_data['assets']['css']) )
						{
							foreach ( $action_data['assets']['css'] as $css_file )
							{
								$this->loadCSS($css_file);
							}
						}
						else
						{
							$this->loadCSS($action_data['assets']['css']);
						}

						if ( is_array($action_data['assets']['js']) )
						{
							foreach ( $action_data['assets']['js'] as $js_file )
							{
								$this->loadJS($js_file);
							}
						}
						else
						{
							$this->loadJS($action_data['assets']['js']);
						}
					}
				}
			}
		}
	}
}