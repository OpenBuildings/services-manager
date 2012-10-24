<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Connects to a S3 bucket and imports Kissmetrics data.
 * Must have 'database', 's3_key', 's3_secret', 's3_object' keys in your config for this to work, and the 'database' to be already created
 * 
 * @author erusev
 * @author Ivan K
 * @copyright (c) 2011-2012 Despark Ltd.
 * @license http://www.opensource.org/licenses/isc-license.txt
 */
class Minion_Task_Kissmetrics_Import extends Minion_Task 
{
	public function execute(array $options)
	{
		$config = Kohana::$config->load('services-manager.reports.kissmetrics');

		if ( ! $config)
			throw new Kohana_Exception('You must set a reports array in the kissmetrics config with "database", "s3_key", "s3_secret", "s3_object"');

		if (count($missing_keys = array_diff(array('database', 's3_key', 's3_secret', 's3_object'), array_keys($config))))
			throw new Kohana_Exception('You must set :keys in your kissmetrics service reports configuration', array(':keys' => join(', ', $missing_keys)));

		require_once Kohana::find_file('vendor', 'aws-sdk-1.5.12/sdk.class');
		
		$database = Database::instance($config['database']);

		CFCredentials::set(array(

			// Credentials for the development environment.
			'development' => array(
				'key' => $config['s3_key'],
				'secret' => $config['s3_secret'],
				'default_cache_config' => '',
				'certificate_authority' => FALSE
			),

			// Specify a default credential set to use if there are more than one.
			'@default' => 'development'
		));
		
		$s3 = new AmazonS3();

		# ~ 
		
		$loaded_paths = DB::select('path')
			->from('files')
			->execute($database)
			->as_array(NULL, 'path');
		
		# ~ 
		
		$year = date('Y');
		
		$options = array(
			 'marker' => $year,
		);
		
		$s3_files = $s3->list_objects($config['s3_object'], $options);
		$s3_files = $s3_files->body->to_array();
		
		$paths = Arr::pluck($s3_files['Contents'], 'Key');

		foreach ($paths as $path)
		{
			if ( ! preg_match('/revisions\/\d+.json/', $path))
			{
				Minion_CLI::write($path, 'yellow');
				continue;				
			}

			if (in_array($path, $loaded_paths))
			{
				Minion_CLI::write($path, 'white');
			}
			else
			{
				Minion_CLI::write($path.'...', 'green');
				
				$file_content = $s3->get_object($config['s3_object'], $path);
				$file_content = $file_content->body;
				
				DB::insert('files', array('path'))->values(array($path))->execute($database);
				
				$km_records = explode("\n", $file_content);

				foreach ($km_records as $km_record)
				{
					$km_record = json_decode($km_record);
					
					if ( ! $km_record)
						continue;
					
					if ( ! isset($km_record->_p))
						throw new Exception('No user associated with the KM event.');

					$moment = date('Y-m-d H:i:s', $km_record->_t);
					
					#
					# Users
					#
					
					# 
					# Creates user.
					
					$query = "
						INSERT IGNORE INTO users
						SET
							generated_name = '$km_record->_p'";
					
					# echo 'insert user: '.$km_record->_p.'<br/>';
					
					$result = $database->query(Database::INSERT, $query);
					
					# 
					# Finds the id of the user. 
					
					$user_id = $result[0];
					
					if ( ! $user_id)
					{
						$query = "
							SELECT id FROM users
							WHERE generated_name = '$km_record->_p'";
						
						$user_id = $database->query(Database::SELECT, $query, TRUE)->current()->id;
					}
					
					# 
					# Sets a given name to the user.
					
					if (isset($km_record->_p2))
					{
						// Should check if one user can have multiple given names.
						DB::update('users')
							->value('given_name', $km_record->_p2)
							->where('id', '=', $user_id)
							->execute($database);
					}
					
					# 
					# Events 
					# 
					
					if (isset($km_record->_n))
					{
						DB::insert('events', array('user', 'name', 'moment'))
							->values(array($user_id, $km_record->_n, $moment))
							->execute($database);
					}
					
					# 
					# Properties
					# 
					
					$non_property_keys = array(
						 '_n', # name
						 '_p', # user generated name
						 '_p2', # user given name
						 '_t', # timestamp
					);

					$properties = DB::insert('properties', array('user', 'name', 'value', 'moment'));
					$sets = array();
					foreach ($km_record as $key => $value)
					{
						if ( ! in_array($key, $non_property_keys))
						{
							$sets[] = array($user_id, $key, $value, $moment);
						}
					}
					if ($sets)
					{
						call_user_func_array(array($properties, 'values'), $sets);

						$properties->execute($database);
					}
				}
				
				DB::update('files')
					->value('loaded', TRUE)
					->execute($database);
				
				Minion_CLI::write_replace($path.'... Done', 'green');
			}
		}
	}
}

