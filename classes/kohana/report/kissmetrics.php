<?php

/**
 * An interface for Kissmetrics Data, imported with the provided minion task
 * 
 * @package    Despark/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 Despark Ltd.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
class Kohana_Report_Kissmetrics extends Report
{
	protected $_event;
	protected $_properties = array();
	
	public function database()
	{
		return Kohana::$config->load('services-manager.reports.kissmetrics.database');
	}
	
	/**
	 * Getter / Setter
	 * Kissmetrics event
	 * 
	 * @param  string $event
	 * @return string|Report_Kissmetrics
	 */
	public function event($event = NULL)
	{
		if ($event !== NULL)
		{
			$this->_event = $event;
			return $this;
		}
		return $this->_event;
	}

	/**
	 * Multi Getter / Setter
	 * Set properties to filter your events by
	 * 
	 * @param  string|array $key
	 * @param  string $value 
	 * @return mixed
	 */
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

	/**
	 * Return how many times the event has occurred filtered by properties
	 * @return integer 
	 */
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
}

