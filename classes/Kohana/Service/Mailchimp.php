<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Exceptionalio service adapter
 * requires 'api-key' configuration
 * 
 * @package    Despark/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 Despark Ltd.
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
		
		if (preg_match('/^list[^s]{1}.*/', $method))
		{
			$args[0] = Arr::path($this->_config, 'lists.'.$args[0], $args[0]);
		}

		$return = call_user_func_array(array($this->_api, $method), $args);
		
		if ($this->_api->errorCode)
		{
			throw new Service_Mailchimp_Exception($this->_api->errorMessage, NULL, $this->_api->errorCode);
		}

		return $return;
	}

	/**
	 * Run Exceptional setup
	 * @return NULL
	 */
	public function init()
	{
		$this->api_key = $this->_config['api-key'];
		$this->_api = new Mailchimp($this->_config['api-key']);
	}

}