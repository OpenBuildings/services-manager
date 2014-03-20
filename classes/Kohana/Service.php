<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * The basic class for managing services
 *
 * Access services with Service::factory()
 *
 * Each service has will be initialiized on first factory() invocation only.
 * If the service is "disabled" it will not rise exceptions, just not work
 *
 * @package    Despark/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 Despark Ltd.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
class Kohana_Service
{
	/**
	 * Caching services
	 *
	 * @var array
	 */
	static public $services = array();

	/**
	 * The cached config file, can be manipulated with the setter
	 *
	 * @var array
	 */
	protected $_config = array();

	/**
	 * If this is false the service will not be initialized, but all of it public methods should still be accessible
	 *
	 * @var boolean
	 */
	protected $_enabled = TRUE;

	/**
	 * Whether the service has been initialized, based on this attributes runs init() only once
	 *
	 * @var boolean
	 */
	private $_initialized = FALSE;

	/**
	 * Get the service. Configuration is in the services-manager under the same name.
	 * The driver name is named the same as the service name
	 *
	 * @param  string $service_name
	 * @return Service
	 */
	public static function factory($service_name)
	{
		if ( ! isset(Service::$services[$service_name]))
		{
			$class = 'Service_'.Service::capitalize_class_name($service_name);

			if ( ! class_exists($class))
				throw new Kohana_Exception('Service :name with class :class does not exist', array(':name' => $service_name, ':class' => $class));

			Service::$services[$service_name] = new $class($service_name);
		}

		return Service::$services[$service_name];
	}

	public static function capitalize_class_name($class_name)
	{
		return str_replace(' ', '_', ucwords(str_replace('_', ' ', $class_name)));
	}

	/**
	 * Getter / setter for enabled attribute
	 *
	 * @param  bool $enabled
	 * @return bool|$this
	 */
	public function enabled($enabled = NULL)
	{
		if ($enabled !== NULL)
		{
			$this->_enabled = (bool) $enabled;

			return $this;
		}

		return $this->_enabled;
	}

	/**
	 * View::capture clone as it is a protected kohana method
	 * @param  string $file      the file to render the contents of
	 * @param  array $variables array of varaiables to be included as local
	 * @return string            the rendered file
	 */
	public function render_file($file, array $variables = array())
	{
		// Import the view variables to local namespace
		extract($variables, EXTR_SKIP);

		// Capture the view output
		ob_start();

		try
		{
			// Load the view within the current scope
			include $file;
		}
		catch (Exception $e)
		{
			// Delete the output buffer
			ob_end_clean();

			// Re-throw the exception
			throw $e;
		}

		// Get the captured output and close the buffer
		return ob_get_clean();
	}

	/**
	 * Getter / setter for config.
	 * If you pass an array, merges it with the current configuraton
	 * If you pass a string returns the config with the specified key
	 *
	 * @param  [type] $config [description]
	 * @return [type]
	 */
	public function config($config = NULL)
	{
		if (is_string($config))
		{
			return Arr::path($this->_config, $config);
		}
		elseif ($config !== NULL)
		{
			$this->_config = Arr::merge($this->_config, (array) $config);
			return $this;
		}

		return $this->_config;
	}

	function __construct($service_name)
	{
		$this->_config = Kohana::$config->load('services-manager.services.'.$service_name);
		$this->_enabled = Arr::get($this->_config, 'enabled');
	}

	/**
	 * Initialize the service, if it's a php service, the library will be loaded here
	 *
	 * @return NULL
	 */
	public function init()
	{

	}

	/**
	 * Check if the service has been initialized, and if not, run init(), return FALSE if disabled
	 *
	 * @return bool
	 */
	public function initialized()
	{
		if ($this->_initialized)
			return TRUE;

		if ($this->enabled())
		{
			if (PHP_SAPI != 'cli' AND ( ! $this->is_enabled_for_user() OR ! $this->is_enabled_for_robots()))
			{
				$this->enabled(FALSE);

				return FALSE;
			}

			$this->init();
			$this->_initialized = TRUE;

			return TRUE;
		}

		return FALSE;
	}


	public function is_enabled_for_robots()
	{
		if (Request::initial() AND Arr::get($this->_config, 'disabled-for-robots'))
		{
			return strpos(Request::initial()->user_agent('robot'), 'Googlebot') === FALSE;
		}

		return TRUE;
	}

	public function is_enabled_for_user()
	{
		if ($role = Arr::get($this->_config, 'disabled-for-role'))
		{
			return ! Auth::instance()->logged_in($role);
		}

		if ($role = Arr::get($this->_config, 'enabled-for-role'))
		{
			return Auth::instance()->logged_in($role);
		}

		return TRUE;
	}

	static public function disable_all()
	{
		$services = func_get_args();

		if (empty($services))
		{
			$services = Service::names();
		}

		foreach ($services as $service_name)
		{
			Service::factory($service_name)->enabled(FALSE);
		}
	}

	static public function enable_all()
	{
		$services = func_get_args();

		if (empty($services))
		{
			$services = Service::names();
		}

		foreach ($services as $service_name)
		{
			Service::factory($service_name)->enabled(TRUE);
		}
	}


	static public function names()
	{
		return array_keys(Kohana::$config->load('services-manager.services'));
	}

	/**
	 * Render enabled javascript services, you can specify a list of services to load, otherwise renders all of them
	 *
	 * @return string
	 */
	static public function all_bodies()
	{
		$services = func_get_args();

		if (empty($services))
		{
			$services = Service::names();
		}

		$bodies = array();

		foreach ($services as $service_name)
		{
			$service = Service::factory($service_name);

			if ($service instanceof Service_Type_Javascript)
			{
				$bodies[] = $service->body();
			}
		}

		return implode("\n", array_filter($bodies));
	}

	static public function all_heads()
	{
		$services = func_get_args();

		if (empty($services))
		{
			$services = Service::names();
		}

		$headers = array();

		foreach ($services as $service_name)
		{
			$service = Service::factory($service_name);

			if ($service instanceof Service_Type_Javascript)
			{
				$headers[] = $service->head();
			}
		}

		return implode("\n", $headers);
	}
}

