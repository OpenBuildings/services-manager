<?php 

defined('SYSPATH') or die('No direct script access.');

/**
 * Get a report for googleanalytics events
 * 
 * @param string metrics the name of the metrics to get (e.g. ga:visits)
 * @param string start_date the starting date, any strtotime format, defaults to yesterday
 * @param string end_date the end of the range date, any strtotime format, defaults to today
 * @param string sort the sorting direction (e.g. -ga:visits)
 * @param integer max_results the maximum results to return
 * @param integer start_index starting index (pagination)
 * @param string dimensions the dimensions (y column)
 * @param string filters filters
 * @param string segment segment
 * 
 */
class Minion_Task_GoogleAnalytics_Report extends Minion_Task 
{
	protected $_config = array(
		'metrics' => FALSE,
		'start_date' => 'yesterday', 
		'end_date' => 'today',
		'sort' => FALSE,
		'max_results' => FALSE,
		'dimensions' => FALSE,
		'filters' => FALSE,
		'segment' => FALSE,
		'start_index' => FALSE,
	);

	public function build_validation(Validation $validation)
	{
		return parent::build_validation($validation)
			->rule('metrics', 'not_empty')
			->rule('start_date', 'strtotime') 
			->rule('end_date', 'strtotime')
			->rule('start_index', 'digit')
			->rule('max_results', 'digit'); 
	}

	public function execute(array $options)
	{
		$options['start_date'] = date('Y-m-d', strtotime($options['start_date']));
		$options['end_date'] = date('Y-m-d', strtotime($options['end_date']));

		$report = Service::factory('googleanalytics')->report();

		$report_params = array();
		foreach ($options as $key => $value) 
		{
			if ($value)
			{
				$report->$key($value);
				$report_params[] = "$key: $value";
			}
		}

		Minion_CLI::write('Total: '.$report->total().' For '.join(', ', $report_params));
	}
}

