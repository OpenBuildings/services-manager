<?php 

defined('SYSPATH') or die('No direct script access.');

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
			->rule('end_date', 'strtotime'); 
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

