<?php

/**
 * Google analytics reporting
 */
abstract class Kohana_Service_GoogleAnalytics_Report
{
	const URL = 'https://www.googleapis.com/analytics/v3/data/ga';

	public static function factory($metrics)
	{
		return new Service_GoogleAnalytics_Report($metrics);
	}

	protected $_metrics;
	protected $_start_date;
	protected $_end_date;
	protected $_sort;
	protected $_max_results;
	protected $_dimensions;
	protected $_project_id;
	protected $_access_token;
	protected $_filters;
	protected $_segment;
	protected $_start_index;
	
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

	public function retrieve()
	{
		return json_decode(@file_get_contents(Service_GoogleAnalytics_Report::URL.'?'.http_build_query($this->request_params())), TRUE);
	}

	public function rows()
	{
		return Arr::get($this->retrieve(), 'rows');
	}

	public function total()
	{
		return Arr::path($this->retrieve(), 'totalsForAllResults.'.$this->_metrics);
	}

	function __construct($metrics)
	{
		$this->_project_id = Service::factory('googleanalytics')->config('project_id');
		$this->_access_token = Service::factory('googleanalytics')->access_token();
		$this->metrics($metrics);
		$this
			->start_date('1 month ago')
			->end_date('today');
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
	
	public function start_date($start_date = NULL)
	{
		if ($start_date !== NULL)
		{
			$this->_start_date = date('Y-m-d', (is_numeric($start_date) ? $start_date : strtotime($start_date)));
			return $this;
		}

		return $this->_start_date;
	}
	
	public function end_date($end_date = NULL)
	{
		if ($end_date !== NULL)
		{
			$this->_end_date = date('Y-m-d', (is_numeric($end_date) ? $end_date : strtotime($end_date)));
			return $this;
		}
		return $this->_end_date;
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

	public function project_id()
	{
		return $this->_project_id;
	}

	public function access_token()
	{
		return $this->_access_token;
	}
}

