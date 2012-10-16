<?php

/**
 * KissMetrics reporting
 */
abstract class Kohana_Service_KissMetrics_Report
{
	public static function factory($event)
	{
		return new Service_KissMetrics_Report($event);
	}

	protected $_event;
	protected $_properties = array();
	protected $_start_date;
	protected $_end_date;
	protected $_database;
	
	public function total()
	{
		$select = DB::select()
			->from('events')
			->select(array(DB::expr('COUNT(DISTINCT events.user)'), 'count'))
			->where('events.name', '=', $this->event())
			->where('events.moment', 'BETWEEN', array($this->start_date(), $this->end_date()));

		if ($this->properties())
		{
			$select
				->join('properties')
				->on('events.user', '=', 'properties.user');

			foreach ($this->properties() as $property => $value) 
			{
				$select
					->on('properties.name', '=', DB::expr("'$property'"))
					->on('properties.value', '=', DB::expr("'$value'"));
			}
		}

		return $select
			->execute($this->database())
			->get('count');
	}

	function __construct($event)
	{
		$this->event($event);
		$this
			->start_date('1 month ago')
			->end_date('today');
	}

	public function database()
	{
		return Kohana::$config->load('services-manager.services.kissmetrics.reports.database');
	}
	
	public function event($event = NULL)
	{
		if ($event !== NULL)
		{
			$this->_event = $event;
			return $this;
		}
		return $this->_event;
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

	public function properties($key = NULL, $value = NULL)
	{
		if ($key === NULL)
			return $this->_properties;
	
		if (is_array($key))
		{
			$this->_properties = $key;
		}
		else
		{
			if ($value === NULL)
				return Arr::get($this->_properties, $key);
	
			$this->_properties[$key] = $value;
		}
	
		return $this;
	}

	
}

