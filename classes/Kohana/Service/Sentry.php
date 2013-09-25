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
	
	/**
	 * Send an exception to Sentry
	 * @param  Exception $exception exception to be logged
	 * @return NULL|string NULL on failure; event id otherwise
	 */
	function capture_exception(Exception $exception)
	{
		if ( ! $this->initialized())
			return NULL;
		
		// Use getIdent to be future-proof when data returned from
		// captureException might be somehow hashed in getIdent
		return $this->client->getIdent($this->client->captureException($exception));
	}
	
	/**
	 * Run Exceptional setup
	 * @return NULL
	 */
	function init()
	{
		$this->client = new Raven_Client($this->_config['dsn'], $this->_config['options']);
		
		$user = Auth::instance()->get_user();
		
		if ($user)
		{
			$this->client->set_user_data($user->id, NULL, array(
				'username' => $user->username,
			));
		}
	}
}
