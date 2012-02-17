<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * The basic class for managing services
 * 
 * Access services with Service::factory()
 * 
 * Each service has will be initialiized on first factory() invocation only. 
 * If the service is "disabled" it will not rise exceptions, just not work
 */
class Kohana_Service
{
	/**
	 * Caching services
	 * @var array
	 */
	static private $_services = array();

	/**
	 * The cached config file, can be manipulated with the setter
	 * @var array
	 */
	protected $_config = array();

	/**
	 * If this is false the service will not be initialized, but all of it public methods should still be accessible
	 * @var boolean
	 */
	protected $_enabled = TRUE;

	/**
	 * Whether the service has been initialized, based on this attributes runs init() only once
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
	static public function factory($service_name)
	{
		if ( ! isset(Service::$_services[$service_name]))
		{
			$config = Kohana::$config->load('services-manager.'.$service_name);
			$class = 'Service_'.ucfirst($service_name);
			Service::$_services[$service_name] = new $class($config);
		}

		return Service::$_services[$service_name];
	}

	/**
	 * Getter / setter for enabled attribute
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

		return $this->enabled;
	}

	/**
	 * Getter / setter for config. If you pass an array, merges it with the current configuraton
	 * @param  [type] $config [description]
	 * @return [type]
	 */
	public function config($config = NULL)
	{
		if ($config !== NULL)
		{
			$this->_config = Arr::merge($this->_config, (array) $config);
			return $this;
		}

		return $this->_config;
	}

	function __construct($config) 
	{
		$this->_config = $config;
	}

	/**
	 * Initialize the service, if it's a php service, the library will be loaded here
	 * @param  array $config
	 * @return NULL
	 */
	abstract function init(array $config);

	/**
	 * Check if the service has been initialized, and if not, run init(), return FALSE if disabled
	 * @return [type]
	 */
	public function initialized()
	{
		if ($this->_initialized)
			return TRUE;

		if ($this->_enabled)
		{
			$this->init();
			$this->_initialized = TRUE;
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Render enabled javascript services, you can specify a list of services to load, otherwise renders all of them
	 * @return string
	 */
	static public function render_all()
	{
		$services = func_get_args();

		if (empty($services))
		{
			$services = array_keys(Kohana::$config->load('services-manager'));
		}

		$renders = array();
		foreach ($services as $service_name) 
		{
			$service = Service::factory($service_name);
			if ($service instanceof Service_Type_Javascript)
			{
				$renders = $service->render();
			}
		}
		return join("\n", $renders);
	}

	static public function all_headers()
	{
		$services = func_get_args();

		if (empty($services))
		{
			$services = array_keys(Kohana::$config->load('services-manager'));
		}

		$headers = array();
		foreach ($services as $service_name) 
		{
			$service = Service::factory($service_name);
			if ($service instanceof Service_Type_Javascript)
			{
				$headers = $service->header();
			}
		}
		return join("\n", $headers);
	}
}