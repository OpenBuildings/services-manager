<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Get a report for addthis events
 * 
 * @param string metric the name of the metric
 * @param string start_date the starting date, any strtotime format, defaults to yesterday
 * @param string end_date the end of the range date, any strtotime format, defaults to today
 */
class Minion_Task_Report_Addthis extends Minion_Task 
{
	protected $_config = array(
		'metric' => FALSE, 
		'start_date' => 'last week', 
		'end_date' => 'today',
	);

	public function build_validation(Validation $validation)
	{
		return parent::build_validation($validation)
			->rule('metric', 'not_empty')
			->rule('metric', 'in_array', array(':value', array('shares', 'clicks', 'subscriptions', 'sharers', 'influencers', 'clickers', 'users', 'searches', 'referers')))
			->rule('start_date', 'strtotime') 
			->rule('end_date', 'strtotime'); 
	}

	public function execute(array $options)
	{
		$report = Report::factory('addthis');
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

