<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * The basic class for managing analytics data.
 * Adds basic consistent timerange setting
 *
 * @package    Despark/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 Despark Ltd.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Report
{

	/**
	 * Get the report. Configuration is in the services-manager under the same name.
	 * The driver name is named the same as the report name
	 *
	 * @param  string $report_name
	 * @return Report
	 */
	static public function factory($report_name)
	{
		$class = 'Report_'.ucfirst($report_name);

		return new $class();
	}

	protected $_start_date;
	protected $_end_date;
	protected $_date_template = 'Y-m-d H:i:s';

	function __construct()
	{
		$this
			->start_date('1 month ago')
			->end_date('today');
	}

	/**
	 * Getter / Setter
	 * Set the timerange start date, defaults to 1 month ago. Normalizes the date to Y-m-d H:i:s
	 *
	 * @param  string $start_date
	 * @return string
	 */
	public function start_date($start_date = NULL)
	{
		if ($start_date !== NULL)
		{
			$this->_start_date = date($this->_date_template, (is_numeric($start_date) ? $start_date : strtotime($start_date)));
			return $this;
		}

		return $this->_start_date;
	}

	/**
	 * Getter / Setter
	 * Set the timerange end date, defaults to today. Normalizes the date to Y-m-d H:i:s
	 *
	 * @param  string $end_date
	 * @return string
	 */
	public function end_date($end_date = NULL)
	{
		if ($end_date !== NULL)
		{
			$this->_end_date = date($this->_date_template, (is_numeric($end_date) ? $end_date : strtotime($end_date)));
			return $this;
		}
		return $this->_end_date;
	}

	abstract public function total();
}

