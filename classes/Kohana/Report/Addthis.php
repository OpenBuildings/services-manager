<?php

/**
 * An interface for Addthis Analytics API. 
 * It is able to return data only a month back.
 * 
 * @package    Despark/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 Despark Ltd.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
class Kohana_Report_Addthis extends Report
{
	protected $_metric;
	protected $_data;
	protected $_domain;
	protected $_date_template = 'Y-m-d';
	protected $_service;
	protected $_url;
	protected $_dimension = 'day';
	protected $_period = 'month';
	
	/**
	 * Getter / Setter
	 * Which metric to count 
	 *
	 * @link http://support.addthis.com/customer/portal/articles/381264-addthis-analytics-api
	 * @param  string $metric
	 * @return string|Report_Addthis
	 */
	public function metric($metric = NULL)
	{
		if ($metric !== NULL)
		{
			$this->_data = NULL;
			$this->_metric = $metric;
			return $this;
		}
		return $this->_metric;
	}

	public function domain($domain = NULL)
	{
		if ($domain !== NULL)
		{
			$this->_domain = $domain;
			return $this;
		}
		return $this->_domain;
	}

	public function url($url = NULL)
	{
		if ($url !== NULL)
		{
			$this->_url = $url;
			return $this;
		}
		return $this->_url;
	}
	
	public function service($service = NULL)
	{
		if ($service !== NULL)
		{
			$this->_service = $service;
			return $this;
		}
		return $this->_service;
	}
	
	public function dimension($dimension = NULL)
	{
		if ($dimension !== NULL)
		{
			$this->_dimension = $dimension;
			return $this;
		}
		return $this->_dimension;
	}	

	public function period($period = NULL)
	{
		if ($period !== NULL)
		{
			$this->_period = $period;
			return $this;
		}
		return $this->_period;
	}

	/**
	 * Get the Raw data from Addthis API, day by day for a month
	 * @return array 
	 */
	public function data()
	{
		if ($this->_data === NULL)
		{
			$config = Kohana::$config->load('services-manager.reports.addthis');
			$pubid = Arr::get($config, 'pubid');
			$request = Request::factory("https://api.addthis.com/analytics/1.0/pub/{$this->metric()}/{$this->dimension()}.json")
				->query(array(
					'pubid' => $pubid,
					'period' => $this->period(),
				))
				->headers('Authorization', 'Basic '.base64_encode(join(':', Arr::extract($config, array('username', 'password')))));
			
			if ($this->domain())
			{
				$request->query('domain', $this->domain());
			}

			if ($this->url())
			{
				$request->query('url', $this->url());
			}

			if ($this->service())
			{
				$request->query('service', $this->service());
			}

			$this->_data = json_decode($request->execute()->body(), TRUE);
			if (array_key_exists('error', $this->_data))
				throw new Kohana_Exception("Addthis returned an error :error", array(':error' => $data));
		}
		return $this->_data;
	}

	public function filter($callback)
	{
		$total = 0;

		foreach ($this->data() as $row)
		{
			if (call_user_func($callback, $row))
			{
				$total += $row[$this->metric()];
			}
		}

		return $total;
	}

	protected function _filter_by_date($row)
	{
		return ($this->start_date() <= $row['date'] AND $this->end_date() >= $row['date']);
	}

	/**
	 * Calculate the metric for the given timerange
	 * @return integer
	 */
	public function total()
	{
		return $this->filter(array($this, '_filter_by_date'));
	}

}

