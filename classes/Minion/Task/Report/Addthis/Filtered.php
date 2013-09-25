<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Get a report for addthis events based on filtered url
 *
 * options:
 * 	- metric: the name of the metric, required
 * 	- filter: regex for filtering urls
 * 	- service: the name of the service, optional
 * 	- domain: the name of the domain, optional
 * 	- period: month, week, day
 * 
 * @param string metric the name of the metric
 * @param string start_date the starting date, any strtotime format, defaults to yesterday
 * @param string end_date the end of the range date, any strtotime format, defaults to today
 */
class Minion_Task_Report_Addthis_Filtered extends Minion_Task 
{
	protected $_config = array(
		'metric' => FALSE, 
		'domain' => FALSE, 
		'filter' => FALSE,
		'service' => FALSE, 
		'period' => FALSE,
	);

	public function build_validation(Validation $validation)
	{
		return parent::build_validation($validation)
			->rule('period', 'in_array', array(':value', array('month', 'week', 'day')))
			->rule('metric', 'not_empty')
			->rule('metric', 'in_array', array(':value', array('shares', 'clicks', 'subscriptions', 'sharers', 'influencers', 'clickers', 'users', 'searches', 'referers')))
			->rule('filter', 'not_empty'); 
	}

	public function execute(array $options)
	{
		$report = Report::factory('addthis');
		$report_params = array();

		$filter = $options['filter'];
		unset($options['filter']);

		foreach ($options as $key => $value) 
		{
			if ($value)
			{
				$report->$key($value);
				$report_params[] = "$key: ".(is_array($value) ? join(', ', $value) : $value);
			}
		}

		$report->dimension('url');

		Minion_CLI::write('Total: '.$report->filter(function($row) use ($filter, $report){
			return preg_match($filter, $row['url']);
		}).' For filter: '.$filter.' params: '.join(', ', $report_params));
	}


}

