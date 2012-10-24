<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Setup the kissmetrics database
 * 
 * @author Ivan K
 * @copyright (c) 2011-2012 Despark Ltd.
 * @license http://www.opensource.org/licenses/isc-license.txt
 */
class Minion_Task_Kissmetrics_Setup extends Minion_Task 
{
	public function execute(array $options)
	{
		$config = Kohana::$config->load('services-manager.services.kissmetrics.reports');

		if ( ! $config)
			throw new Kohana_Exception('You must set a reports array in the kissmetrics config with "database"');

		if (count($missing_keys = array_diff(array('database'), array_keys($config))))
			throw new Kohana_Exception('You must set :keys in your kissmetrics service reports configuration', array(':keys' => join(', ', $missing_keys)));

		$database = Database::instance($config['database']);

		Minion_CLI::write('creating table files ...');

		$database->query(NULL, "CREATE TABLE IF NOT EXISTS `files` (
		  `path` varchar(255) NOT NULL,
		  `loaded` tinyint(1) NOT NULL,
		  `moment` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


		Minion_CLI::write('creating table events ...');

		$database->query(NULL, "CREATE TABLE IF NOT EXISTS `events` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `user` int(10) unsigned NOT NULL,
		  `name` varchar(255) NOT NULL,
		  `moment` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `user` (`user`),
		  KEY `name` (`name`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8");

		Minion_CLI::write('creating table properties ...');

		$database->query(NULL, "CREATE TABLE IF NOT EXISTS `properties` (
		  `user` int(10) unsigned NOT NULL,
		  `name` varchar(64) NOT NULL,
		  `value` text NOT NULL,
		  `moment` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Minion_CLI::write('creating table users ...');

		$database->query(NULL, "CREATE TABLE IF NOT EXISTS `users` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `generated_name` varchar(255) NOT NULL,
		  `given_name` varchar(255) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `generated_name` (`generated_name`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8");

		Minion_CLI::write('all done', 'green');
	}
}

