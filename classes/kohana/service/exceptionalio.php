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
abstract class Kohana_Service_Exceptionalio extends Service implements Service_Type_Php
{
	public $api_key;

	/**
	 * Log the exception with Exceptional.io. If there is an exception in the library itself, log it to the error log
	 * @param  Exception $exception exception to be logged
	 * @return NULL
	 */
	public function log(Exception $exception)
	{
		if ( ! $this->initialized())
			return NULL;
		
		try
		{
			if (Request::current())
			{
				Exceptional::$controller = Request::current()->controller();
				Exceptional::$action = Request::current()->action();
			}

			if ($this->_config['use-auth'] AND Auth::instance()->logged_in() AND Auth::instance()->get_user())
			{
				Exceptional::context(array(
					'id' => Auth::instance()->get_user()->id,
					'name' => Auth::instance()->get_user()->email
				));
			}

			if ($exception->getCode() == 404)
			{
				// Exceptional::handle_exception(new Http404Error($exception->getMessage()), FALSE);
			}
			else
			{
				Exceptional::handle_exception($exception, FALSE);
			}
		}
		catch (Exception $local_exception) 
		{
			Log::instance(Log::ERROR, $local_exception->getMessage());
		}
	}

	/**
	 * Run Exceptional setup
	 * @return NULL
	 */
	public function init()
	{
		$this->api_key = $this->_config['api-key'];

		require_once Kohana::find_file("vendor/exceptional", "exceptional");
		Exceptional::setup($this->api_key, NULL, FALSE);	
	}

}