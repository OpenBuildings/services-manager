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
abstract class Kohana_Service_Swifttype extends Service
{
	const API = 'https://api.swiftype.com/api/v1/';
	const EXT = '.json';
	public $_api;

	public function api($url, array $post_data = NULL)
	{
		$request = Request::factory(Service_Swifttype::API.$url.Service_Swifttype::EXT);
		$request->headers('Content-Type', 'application/json');

		if ($data !== NULL)
		{
			$post_data['auth_token'] = $this->config('auth_token');
			$request
				->method(Request::POST)
				->post($post_data);
		}
		else
		{
			$request->query('auth_token', $this->config('auth_token'));
		}
		$result = $request->execute();
		return $this->_api;
	}
}