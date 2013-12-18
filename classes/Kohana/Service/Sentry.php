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
abstract class Kohana_Service_Sentry extends Service
{
	private $client;

	private $user_data = array('id' => NULL, 'email' => NULL, 'data' => NULL);
	
	/**
	 * Send an exception to Sentry
	 * @param  Exception $exception exception to be logged
	 * @return NULL|string NULL on failure; event id otherwise
	 */
	function capture_exception(Exception $exception, array $data = array())
	{
		if ( ! $this->initialized())
			return NULL;

		$id = NULL;
		$email = NULL;

		if (Auth::instance() AND ($user = Auth::instance()->get_user())) 
		{
			$id = $user->id();
			$email = $user->email;
		}

		return $this->send_exception_with_user_data($exception, $id, $email, $data);
	}

	public function send_exception_with_user_data(Exception $exception, $id = NULL, $email = NULL, array $data = array())
	{
		$this->client()->set_user_data($id, $email, $data);	

		// Use getIdent to be future-proof when data returned from
		// captureException might be somehow hashed in getIdent
		return $this->client()->getIdent($this->client->captureException($exception));
	}

	public function client()
	{
		return $this->client;
	}
	
	/**
	 * Run Exceptional setup
	 * @return NULL
	 */
	function init()
	{
		$this->client = new Raven_Client($this->_config['dsn'], $this->_config['options']);
	}
}
