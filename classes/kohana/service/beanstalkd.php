<?php defined('SYSPATH') OR die('No direct script access.');

/**
* Beanstalkd service adapter
* requires 'server' configuration
*/
class Kohana_Service_Beanstalkd extends Service implements Service_Type_Php
{
	public $_pheanstalk;

	public function pheanstalk()
	{
		if ( ! $this->initialized())
			return NULL;

		return $this->_pheanstalk;
	}

	public function __call($method, $args)
	{
		if ( ! $this->initialized())
			return NULL;
		
		$return = call_user_func_array(array($this->_pheanstalk, $method), $args);
		
		return $return instanceof Pheanstalk ? $this : $return;
	}

	public function process_tube($tube_name)
	{
		if ( ! $this->initialized())
			return NULL;

		$job = $this->_pheanstalk->watch($tube_name)->reserve();

		if ($job)
		{
			$tube = Service_Beanstalkd_Tube::factory($tube_name);
			$tube->process_job($job);
			unset($tube);
			$this->_pheanstalk->delete($job);
		}
	}

	/**
	 * Run Exceptional setup
	 * @return NULL
	 */
	public function init()
	{
		require_once Kohana::find_file("vendor/pheanstalk", 'pheanstalk_init');

		$this->_pheanstalk = new Pheanstalk($this->_config['server']);
	}

}