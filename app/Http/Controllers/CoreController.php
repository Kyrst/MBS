<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class CoreController extends Controller
{
	use ValidatesRequests;

	const SECTION_ALL = 'all';
	const SECTION_LAYOUT = 'layout';
	const SECTION_CONTENT = 'content';
	const SECTION_JS = 'js';

	const ASSET_CSS = 'css';
	const ASSET_JS = 'js';

	const LIBRARY_LOAD_PRIORITY_PRE = 'pre';
	const LIBRARY_LOAD_PRIORITY_POST = 'post';

	private $layout_view_filename;
	private $layout_view;

	protected $current_controller = [];
	protected $current_action = [];
	protected $current_page;

	protected $base_url;

	protected $ui;

	protected $is_ajax;
	protected $ajax;

	private $data =
	[
		self::SECTION_LAYOUT => [],
		self::SECTION_CONTENT => [],
		self::SECTION_JS => []
	];

	private $assets =
	[
		self::ASSET_CSS => [],
		self::ASSET_JS => []
	];

	private $libraries = [];
	private $loaded_libraries =
	[
		self::LIBRARY_LOAD_PRIORITY_PRE => [],
		self::LIBRARY_LOAD_PRIORITY_POST => []
	];

	public function afterLayoutInit()
	{
	}

	protected function initLayout($method, $parameters)
	{
		if ( !isset($this->layout) )
		{
			throw new \Exception('No layout view set.');
		}

		$this->layout_view_filename = 'layouts/' . $this->layout;
		$this->layout_view = view($this->layout_view_filename);

		$current_controller = $this->getController();

		$this->current_controller =
		[
			'original' => $current_controller,
			'hyphen' => \App\Helpers\Core\Str::slashToHyphen(\App\Helpers\Core\Str::camelCaseToHyphen($method)),
			'underscore' => \App\Helpers\Core\Str::hyphenToUnderscore($current_controller)
		];

		$this->current_action =
		[
			'original' => $method,
			'underscore' => \App\Helpers\Core\Str::camelCaseToUnderscore($method),
			'hyphen' => \App\Helpers\Core\Str::camelCaseToHyphen($method)
		];

		$this->current_page = $current_controller . '/' . $this->current_action['hyphen'];

		$this->base_url = \URL::route('home', [], FALSE);
		$this->assign('base_url', $this->base_url, [self::SECTION_LAYOUT, self::SECTION_JS]);

		$this->ui = new \App\Helpers\Core\UI();

		if ( $this->ui->haveMessage() )
		{
			$this->assign('core_message', $this->ui->getMessage(), 'js');

			$this->ui->deleteMessage();
		}

		$this->is_ajax = (\Request::ajax() === TRUE);
		$this->ajax = new \App\Helpers\Core\Ajax($this->ui);

		// CSRF
		$csrf_token = csrf_token();

		$this->assign('csrf_token', $csrf_token, self::SECTION_CONTENT);

		$encrypter = app('Illuminate\Encryption\Encrypter');
		$encrypted_csrf_token = $encrypter->encrypt($csrf_token);

		$this->assign('csrf_token', $csrf_token, self::SECTION_JS);
		$this->assign('encrypted_csrf_token', $encrypted_csrf_token, self::SECTION_JS);

		$this->afterLayoutInit();
	}

	protected function getController()
	{
		$controller = \Illuminate\Support\Str::parseCallback(\Route::currentRouteAction(), null)[0];

		if ( !preg_match('/^App\\\\Http\\\\Controllers\\\\([a-zA-Z\\\\]+)Controller$/', $controller, $controller_matches) )
		{
			throw new \Exception('Could not parse controller.');
		}

		return strtolower(str_replace('\\', '/', \App\Helpers\Core\Str::camelCaseToHyphen($controller_matches[1])));
	}

	public function assign($key, $value, $section = self::SECTION_CONTENT)
	{
		$assign = function($section, $key, $value)
		{
			if ( isset($this->data[$section][$key]) )
			{
				throw new \Exception('Key "' . $key . '" already assiged.');
			}

			$this->data[$section][$key] = $value;
		};

		if ( $section === self::SECTION_ALL )
		{
			$section = [self::SECTION_LAYOUT, self::SECTION_CONTENT, self::SECTION_JS];
		}

		if ( is_array($section) )
		{
			$sections = (array)$section;

			foreach ( $sections as $section )
			{
				$assign($section, $key, $value);
			}
		}
		else
		{
			$assign($section, $key, $value);
		}
	}

	protected function addLibrary($library_name, $css_files = NULL, $js_files = NULL)
	{
		$this->libraries[$library_name] =
			[
				self::ASSET_CSS => $css_files,
				self::ASSET_JS => $js_files
			];
	}

	protected function loadCSS($path, $external = FALSE)
	{
		if ( \App::environment() === 'local' )
		{
			foreach ( $this->assets[self::ASSET_CSS] as $css_file )
			{
				if ( $css_file['path'] === $path )
				{
					throw new \Exception('Stylesheet "' . $path . '" already added.');
				}
			}
		}

		$this->assets[self::ASSET_CSS][] =
		[
			'path' => $path,
			'external' => $external
		];
	}

	protected function loadJS($path, $external = FALSE)
	{
		if ( \App::environment() === 'local' )
		{
			foreach ( $this->assets[self::ASSET_JS] as $js_file )
			{
				if ( $js_file['path'] === $path )
				{
					throw new \Exception('JavaScript "' . $path . '" already added.');
				}
			}
		}

		$this->assets[self::ASSET_JS][] =
		[
			'path' => $path,
			'external' => $external
		];
	}

	protected function loadLibrary($library_name, $load_priority = self::LIBRARY_LOAD_PRIORITY_PRE)
	{
		if ( !isset($this->libraries[$library_name]) )
		{
			throw new \Exception('Library "' . $library_name . '" does not exist.');
		}

		$this->loaded_libraries[$load_priority][] = $library_name;
	}

	private function includeCSS($css)
	{
		if ( is_array($css) )
		{
			foreach ( $css as $css_file )
			{
				$this->loadCSS($css_file);
			}
		}
		else
		{
			$this->loadCSS($css);
		}
	}
	private function includeJS($js)
	{
		if ( is_array($js) )
		{
			foreach ( $js as $js_file )
			{
				$this->loadJS($js_file);
			}
		}
		else
		{
			$this->loadJS($js);
		}
	}

	private function includeLibrary($library_name)
	{
		if ( !isset($this->libraries[$library_name]) )
		{
			throw new \Exception('Could not find library "' . $library_name . '".');
		}

		$library = $this->libraries[$library_name];

		if ( isset($library[self::ASSET_CSS]) )
		{
			$this->includeCSS($library[self::ASSET_CSS]);
		}

		if ( isset($library[self::ASSET_JS]) )
		{
			$this->includeJS($library[self::ASSET_JS]);
		}
	}

	private function includeAssets($libraries, array $css_files, array $js_files)
	{
		// Pre-loaded libraries
		foreach ( $this->loaded_libraries[self::LIBRARY_LOAD_PRIORITY_PRE] as $lib_name )
		{
			$this->includeLibrary($lib_name);
		}

		// Libraries loaded from display() function
		foreach ( $libraries as $lib_name )
		{
			$this->includeLibrary($lib_name);
		}

		// Post-loaded libraries
		foreach ( $this->loaded_libraries[self::LIBRARY_LOAD_PRIORITY_POST] as $lib_name )
		{
			$this->includeLibrary($lib_name);
		}

		if ( $this->current_controller['underscore'] === 'dashboard/home' )
		{
			$this->includeJS('js/libs/audio_player_manager/audio_player_manager_song.js');
			$this->includeJS('js/libs/audio_player_manager/audio_player_manager.js');
		}

		// Auto load layout CSS & JS
		$css_layout_filename = 'css/' . str_replace('.', '/', $this->layout_view->getName()) . '.css';
		$css_layout_path = public_path() . '/' . $css_layout_filename;

		if ( file_exists($css_layout_path) )
		{
			$this->loadCSS($css_layout_filename);
		}

		$js_layout_filename = 'js/' . str_replace('.', '/', $this->layout_view->getName()) . '.js';
		$js_layout_path = public_path() . '/' . $js_layout_filename;

		if ( file_exists($js_layout_path) )
		{
			$this->loadJS($js_layout_filename);
		}

		// Automatically load CSS & JS based off route
		$css_short_auto_path = 'css/' . $this->current_controller['underscore'] . '/' . $this->current_action['underscore'] . '.css';
		$css_auto_path = public_path() . '/' . $css_short_auto_path;

		if ( file_exists($css_auto_path) )
		{
			$this->includeCSS($css_short_auto_path);
		}

		$js_short_auto_path = 'js/' . $this->current_controller['underscore'] . '/' . $this->current_action['underscore'] . '.js';
		$js_auto_path = public_path() . '/' . $js_short_auto_path;

		if ( file_exists($js_auto_path) )
		{
			$this->includeJS($js_short_auto_path);
		}

		// CSS loaded from display() function
		foreach ( $css_files as $css_file )
		{
			$this->includeCSS($css_file);
		}

		// JS loaded from display() function
		foreach ( $js_files as $js_file )
		{
			$this->includeJS($js_file);
		}
	}

	private function includeContentData()
	{
		$this->assign('current_page', $this->current_page, self::SECTION_ALL);
		$this->assign('page_id', \App\Helpers\Core\Str::slashToUnderscore($this->current_controller['underscore'] . '_' . $this->current_action['underscore']) . '_page', self::SECTION_LAYOUT);

		foreach ( $this->data[self::SECTION_LAYOUT] as $key => $value )
		{
			$this->layout_view->$key = $value;
		}

		$this->layout_view->assets = $this->assets;
	}

	private function getContentData()
	{
		return array_merge
		(
			$this->data[self::SECTION_CONTENT],
			['js_vars' => $this->data[self::SECTION_JS]]
		);
	}

	private function generatePageTitle($page_title, $page_title_suffix)
	{
		if ( $page_title !== NULL )
		{
			return ((is_array($page_title) ? implode(' | ', $page_title) : $page_title) . ($page_title_suffix ? ' ' . \Config::get('custom.PAGE_TITLE_SEPARATOR') . ' ' . \Config::get('custom.PAGE_TITLE_SUFFIX') : ''));
		}
		else
		{
			return \Config::get('custom.DEFAULT_PAGE_TITLE');
		}
	}

	private function includejQuery()
	{
		$jquery_view = view('layouts/partials/jquery');
		$jquery_view->base_url = $this->base_url;

		$this->layout_view->jquery = $jquery_view->render();
	}

	private function includeInlineJS()
	{
		$inline_js_view = view('layouts/partials/inline_js');
		$inline_js_view->js_variables = $this->data[self::ASSET_JS];

		$this->layout_view->inline_js = $inline_js_view->render();
	}

	public function beforeDisplay()
	{
	}

	public function display($page_title = NULL, $page_title_suffix = TRUE, array $libraries = [], array $css_files = [], array $js_files = [], $view_file = NULL)
	{
		$this->beforeDisplay();

		$this->layout_view->page_title = ($page_title !== NULL ? $this->generatePageTitle($page_title, $page_title_suffix) : \Config::get('custom.DEFAULT_PAGE_TITLE'));

		$this->includeAssets($libraries, $css_files, $js_files);
		$this->includeContentData();

		$this->includejQuery();
		$this->includeInlineJS();

		if ( $view_file === NULL )
		{
			$view_file = $this->current_controller['underscore'] . '/' . $this->current_action['underscore'];
		}

		return $this->layout_view->nest
		(
			'content',
			$view_file,
			$this->getContentData()
		);
	}

	public function getCurrentController()
	{
		return $this->current_controller['original'];
	}

	public function getCurrentAction()
	{
		return $this->current_action['original'];
	}
}