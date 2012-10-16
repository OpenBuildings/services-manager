<?php 

defined('SYSPATH') or die('No direct script access.');

class Minion_Task_Kissmetrics_Report extends Minion_Task 
{
	protected $_config = array(
		'event' => FALSE, 
		'start_date' => 'yesterday', 
		'end_date' => 'today',
	);

	public function build_validation(Validation $validation)
	{
		return parent::build_validation($validation)
			->rule('event', 'not_empty')
			->rule('start_date', 'strtotime') 
			->rule('end_date', 'strtotime'); 
	}

	public function execute(array $options)
	{
		$report = Service::factory('kissmetrics')->report();

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

