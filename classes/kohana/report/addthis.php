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
	protected $_date_template = 'Y-m-d';
	
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
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_USERPWD => join(':', Arr::extract($config, array('username', 'password'))),
				CURLOPT_URL => "https://api.addthis.com/analytics/1.0/pub/{$this->metric()}/day.json?pubid={$pubid}&period=month",
				CURLOPT_RETURNTRANSFER => TRUE,
			));
			$data = curl_exec($curl);
			$this->_data = json_decode($data, TRUE);
			if (array_key_exists('error', $this->_data))
				throw new Kohana_Exception("Addthis returned an error :error", array(':error' => $data));
		}
		return $this->_data;
	}

	/**
	 * Calculate the metric for the given timerange
	 * @return integer
	 */
	public function total()
	{
		$total = 0;

		foreach ($this->data() as $day) 
		{
			if ($this->start_date() <= $day['date'] AND $this->end_date() >= $day['date'])
			{
				$total += $day[$this->metric()];
			}
		}

		return $total;
	}
}

