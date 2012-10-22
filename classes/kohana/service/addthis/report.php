<?php

/**
 * Local database reporting
 */
class Kohana_Service_Addthis_Report
{
	public static function factory($metric = NULL)
	{
		return new Service_Addthis_Report($metric = NULL);
	}

	protected $_start_date;
	protected $_end_date;
	protected $_metric;
	protected $_data;
	
	function __construct($metric = NULL)
	{
		$this->metric($metric);
		$this
			->start_date('1 month ago')
			->end_date('today');
	}


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

	
	public function data()
	{
		if ($this->_data === NULL)
		{
			$config = Kohana::$config->load('services-manager.services.addthis');
			$pubid = Arr::get($config, 'api-key');
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

	public function total()
	{
		$total = 0;

		foreach ($this->data() as $day) 
		{
			if ($this->start_date() <= $day['date'] AND $this->end_date() > $day['date'])
			{
				$total += $day[$this->metric()];
			}
		}

		return $total;
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
	
}

