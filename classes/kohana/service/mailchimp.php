<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Exceptionalio service adapter
 * requires 'api-key' configuration
 * 
 * @package    OpenBuildings/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 OpenBuildings Inc.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Service_Mailchimp extends Service implements Service_Type_Php
{
	public $_api;

	public function api()
	{
		if ( ! $this->initialized())
			return NULL;

		return $this->_api;
	}

	public function __call($method, $args)
	{
		if ( ! $this->initialized())
			return NULL;
		
		if (strpos($method, 'list') == 0)
		{
			$args[0] = Arr::path($this->_config, 'lists.'.$args[0], $args[0]);
		}

		call_user_func_array(array($this->_api, $method), $args);
		
		if ($this->_api->errorCode)
		{
			throw new Kohana_Exception("Mailchimp Exception: :message", array(':message' => $this->_api->errorMessage));
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

		$this->_api = new MCAPI($this->_config['api-key']);
	}

}