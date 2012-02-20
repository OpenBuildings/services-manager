<?php defined('SYSPATH') OR die('No direct script access.');

/**
* Exceptionalio service adapter
* requires 'api-key' configuration
*/
abstract class Kohana_Service_Mailchimp extends Service implements Service_Type_Php
{
	public $_api;

	public function api()
	{
		if ( ! $this->initialized())
			return NULL;

		return $this->_color
	}

	public function __call($method, $args)
	{
		if ( ! $this->initialized())
			return NULL;
		
		if (strpos('list') == 0)
		{
			$args[0] = Arr::path($this->_config, 'lists.'.$args[0], $args[0]);
		}

		call_user_func_array(array($this->_api, $method), $args);
		
		if ($this->_api->errorCode)
		{
			throw new Koahana_Exception("Mailchimp Exception: :message", array(':message', $this->_api->errorMessage));
		}

	}

	/**
	 * Run Exceptional setup
	 * @return NULL
	 */
	public function init()
	{
		require_once Kohana::find_file("vendor", 'MCAPI.class');

		$this->api_key = $this->_config['api-key'];

		$this->_color = new MCAPI($this->_config['api-key']));
	}

}