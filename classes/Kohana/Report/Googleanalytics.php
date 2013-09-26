<?php

use OAuth\OAuth2\Service\Google;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

/**
 * An interface for google analytics API
 * 
 * @package    Despark/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 Despark Ltd.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
class Kohana_Report_Googleanalytics extends Report
{
	const URL = 'https://www.googleapis.com/analytics/v3/data/ga';

	protected $_metrics;
	protected $_sort;
	protected $_max_results;
	protected $_dimensions;
	protected $_project_id;
	protected $_access_token;
	protected $_filters;
	protected $_segment;
	protected $_start_index;
	protected $_date_template = 'Y-m-d';
	
	/**
	 * Build all the request query parameters needed to access the google analytics API
	 * @return array 
	 */
	public function request_params()
	{
		$data = array(
			'ids' => $this->project_id(),
			'access_token' => $this->access_token(),
			'start-date' =>  $this->start_date(),
			'end-date' => $this->end_date(),
			'metrics' => $this->metrics(),
			'dimensions' => $this->dimensions(),
			'max-results' => $this->max_results(),
			'sort' => $this->sort(),
			'filters' => $this->filters(),
			'segment' => $this->segment(),
			'start-index' => $this->start_index(),
		);
		return array_filter($data);
	}

	/**
	 * Return the project_id set in the config or set it for this report
	 * @return string
	 */
	public function project_id($project_id = NULL)
	{
		if ($project_id !== NULL)
		{
			$this->_project_id = $project_id;
			return $this;
		}

		if ( ! $this->_project_id)
		{
			$this->_project_id = Kohana::$config->load('services-manager.reports.googleanalytics.project_id');
		}
		return $this->_project_id;
	}

	/**
	 * Generate a new access token for google analytics API using client_id, client_secret and refresh_token, set in the config
	 * @return string 
	 */
	public function access_token()
	{
		if ( ! $this->_access_token)
		{
			$config = Kohana::$config->load('services-manager.reports.googleanalytics');

			if (count($missing_keys = array_diff(array('refresh_token', 'client_id', 'client_secret'), array_keys($config))))
				throw new Kohana_Exception('Must set :keys for googleanalytics service configuration', array(':keys' => join(', ', $missing_keys)));

			// require_once Kohana::find_file("vendor", "googleoauth");

			// Session storage
			$storage = new Session();
			// Setup the credentials for the requests
			$credentials = new Credentials(
				$config['client_id'],
				$config['client_secret'],
				Request::current()->url()
			);

			$serviceFactory = new \OAuth\ServiceFactory();
			// Instantiate the Google service using the credentials, http client and storage mechanism for the token
			$googleService = $serviceFactory->createService('google', $credentials, $storage, array('userinfo_email', 'userinfo_profile'));
			$tokenInterface = new \OAuth\OAuth2\Token\StdOAuth2Token(NULL, $config['refresh_token']);
			$token = $googleService->refreshAccessToken($tokenInterface);
			$this->_access_token = $token->getAccessToken();
		}

		return $this->_access_token;
	}

	/**
	 * Get the result from calling Google Anlaytics API
	 * @return array 
	 */
	public function retrieve()
	{
		return json_decode(Request::factory(Report_GoogleAnalytics::URL)->query($this->request_params())->execute()->body(), TRUE);
	}

	/**
	 * Rows from Google Analytics API response
	 * @return array 
	 */
	public function rows()
	{
		return (array) Arr::get($this->retrieve(), 'rows');
	}

	/**
	 * Get 'totals' from Google Analytics API response
	 * @return mixed
	 */
	public function total()
	{
		return Arr::path($this->retrieve(), 'totalsForAllResults.'.$this->metrics());
	}

	/**
	 * Getter / Setter
	 * The maximum number of rows to include in the response.
	 *
	 * @link https://developers.google.com/analytics/devguides/reporting/core/v3/reference#maxResults
	 * @param  string $max_results
	 * @return string|Report_GoogleAnalytics
	 */
	public function max_results($max_results = NULL)
	{
		if ($max_results !== NULL)
		{
			$this->_max_results = $max_results;
			return $this;
		}
		return $this->_max_results;
	}
	
	/**
	 * Getter / Setter
	 * A list of comma-separated dimensions for your Analytics data, such as ga:browser,ga:city.
	 *
	 * @link https://developers.google.com/analytics/devguides/reporting/core/v3/reference#dimensions
	 * @param  string $dimensions
	 * @return string|Report_GoogleAnalytics
	 */
	public function dimensions($dimensions = NULL)
	{
		if ($dimensions !== NULL)
		{
			$this->_dimensions = $dimensions;
			return $this;
		}
		return $this->_dimensions;
	}
	
	/**
	 * Getter / Setter
	 * A list of comma-separated metrics, such as ga:visits,ga:bounces.
	 *
	 * @link https://developers.google.com/analytics/devguides/reporting/core/v3/reference#metrics 
	 * @param  string $metrics 
	 * @return string|Report_GoogleAnalytics
	 */
	public function metrics($metrics = NULL)
	{
		if ($metrics !== NULL)
		{
			$this->_metrics = $metrics;
			return $this;
		}
		return $this->_metrics;
	}
	
	/**
	 * Getter / Setter
	 * A list of comma-separated dimensions and metrics indicating the sorting order and sorting direction for the returned data.
	 *
	 * @link https://developers.google.com/analytics/devguides/reporting/core/v3/reference#sort
	 * @param  string $sort
	 * @return string|Report_GoogleAnalytics
	 */
	public function sort($sort = NULL)
	{
		if ($sort !== NULL)
		{
			$this->_sort = $sort;
			return $this;
		}
		return $this->_sort;
	}

	/**
	 * Getter / Setter
	 * Dimension or metric filters that restrict the data returned for your request.
	 *
	 * @link https://developers.google.com/analytics/devguides/reporting/core/v3/reference#filters
	 * @param  string $filters 
	 * @return string|Report_GoogleAnalytics
	 */
	public function filters($filters = NULL)
	{
		if ($filters !== NULL)
		{
			$this->_filters = $filters;
			return $this;
		}
		return $this->_filters;
	}	

	/**
	 * Getter / Setter
	 * Segments the data returned for your request.
	 *
	 * @link https://developers.google.com/analytics/devguides/reporting/core/v3/reference#segment
	 * @param  string $segment
	 * @return string|Report_GoogleAnalytics
	 */
	public function segment($segment = NULL)
	{
		if ($segment !== NULL)
		{
			$this->_segment = $segment;
			return $this;
		}
		return $this->_segment;
	}

	/**
	 * Getter / Setter
	 * The first row of data to retrieve, starting at 1. Use this parameter as a pagination mechanism along with the max-results parameter.
	 *
	 * @link https://developers.google.com/analytics/devguides/reporting/core/v3/reference#startIndex
	 * @param  string $start_index 
	 * @return string|Report_GoogleAnalytics
	 */
	public function start_index($start_index = NULL)
	{
		if ($start_index !== NULL)
		{
			$this->_start_index = $start_index;
			return $this;
		}
		return $this->_start_index;
	}
}

