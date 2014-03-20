<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Get a report for addthis events
 *
 * options:
 * 	- metric: the name of the metric, required
 * 	- url: the name of the url, optional
 * 	- domain: the name of the domain, optional
 * 	- start_date: the starting date, any strtotime format, defaults to yesterday
 * 	- end_date: the end of the range date, any strtotime format, defaults to today
 *
 */
class Minion_Task_Report_Addthis extends Minion_Task
{
	protected $_config = array(
		'metric' => FALSE,
		'domain' => FALSE,
		'url' => FALSE,
		'service' => FALSE,
		'start_date' => 'last week',
		'end_date' => 'today',
	);

	public function build_validation(Validation $validation)
	{
		return parent::build_validation($validation)
			->rule('metric', 'not_empty')
			->rule('metric', 'in_array', array(':value', array('shares', 'clicks', 'subscriptions', 'sharers', 'influencers', 'clickers', 'users', 'searches', 'referers')))
			->rule('start_date', 'strtotime')
			->rule('end_date', 'strtotime')
			->rule('url', 'url');
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

