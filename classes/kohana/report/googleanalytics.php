<?php

/**
 * Local database reporting
 */
class Kohana_Report_GoogleAnalytics extends Report
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

	public function project_id()
	{
		return Kohana::$config->load('services-manager.reports.googleanalytics.project_id');
	}

	public function access_token()
	{
		if ( ! $this->_access_token)
		{
			$config = Kohana::$config->load('services-manager.reports.googleanalytics');

			if (count($missing_keys = array_diff(array('refresh_token', 'client_id', 'client_secret'), array_keys($config))))
				throw new Kohana_Exception('Must set :keys for googleanalytics service configuration', array(':keys' => join(', ', $missing_keys)));

			require_once Kohana::find_file("vendor", "googleoauth");

			$auth = new GoogleOAuth($config['client_id'], $config['client_secret']);
			
			$this->_access_token = $auth->obtain_access_token($config['refresh_token']);
		}

		return $this->_access_token;
	}

	public function retrieve()
	{
		return json_decode(@file_get_contents(Report_GoogleAnalytics::URL.'?'.http_build_query($this->request_params())), TRUE);
	}

	public function rows()
	{
		return (array) Arr::get($this->retrieve(), 'rows');
	}

	public function total()
	{
		return Arr::path($this->retrieve(), 'totalsForAllResults.'.$this->metrics());
	}

	public function max_results($max_results = NULL)
	{
		if ($max_results !== NULL)
		{
			$this->_max_results = $max_results;
			return $this;
		}
		return $this->_max_results;
	}
	
	public function dimensions($dimensions = NULL)
	{
		if ($dimensions !== NULL)
		{
			$this->_dimensions = $dimensions;
			return $this;
		}
		return $this->_dimensions;
	}
	
	public function metrics($metrics = NULL)
	{
		if ($metrics !== NULL)
		{
			$this->_metrics = $metrics;
			return $this;
		}
		return $this->_metrics;
	}
	
	public function sort($sort = NULL)
	{
		if ($sort !== NULL)
		{
			$this->_sort = $sort;
			return $this;
		}
		return $this->_sort;
	}

	public function filters($filters = NULL)
	{
		if ($filters !== NULL)
		{
			$this->_filters = $filters;
			return $this;
		}
		return $this->_filters;
	}	

	public function segment($segment = NULL)
	{
		if ($segment !== NULL)
		{
			$this->_segment = $segment;
			return $this;
		}
		return $this->_segment;
	}

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

