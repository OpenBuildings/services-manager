<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Get a report for kissmetrics events
 * 
 * @param string event the name of the event
 * @param string properties limit events based on user property. e.g. --properties="type=professional&test=test"
 * @param string start_date the starting date, any strtotime format, defaults to yesterday
 * @param string end_date the end of the range date, any strtotime format, defaults to today
 */
class Minion_Task_Report_Kissmetrics extends Minion_Task 
{
	protected $_config = array(
		'event' => FALSE, 
		'start_date' => 'yesterday', 
		'end_date' => 'today',
		'properties' => '',
	);

	public function build_validation(Validation $validation)
	{
		return parent::build_validation($validation)
			->rule('event', 'not_empty')
			->rule('properties', 'json_decode')
			->rule('start_date', 'strtotime') 
			->rule('end_date', 'strtotime'); 
	}

	public function execute(array $options)
	{
		parse_str($options['properties'], $options['properties']);
		$report = Report::factory('kissmetrics');
		$report_params = array();
		foreach ($options as $key => $value) 
		{
			if ($value)
			{
				$report->$key($value);
				$report_params[] = "$key: ".(is_array($value) ? join(', ', $value) : $value);
			}
		}
		Minion_CLI::write('Total: '.$report->total().' For '.join(', ', $report_params));
	}
}

