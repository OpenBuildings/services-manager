<?php

/**
 * Local database reporting
 */
class Kohana_Report_Addthis extends Report
{
	protected $_metric;
	protected $_data;
	protected $_date_template = 'Y-m-d';
	
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

