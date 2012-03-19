<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Kohana_Service_Beanstalkd_Tube {

	protected $_tube_name;
	protected $_refresh_database = TRUE;

	function __construct($tube_name) 
	{
		$this->_tube_name = $tube_name;

		if ($this->_refresh_database)
		{
			Database::instance()->connect();	
		}
	}

	public function process_job(Pheanstalk_Job $job)
	{
		try 
		{
			$this->execute($job->getData());
		} 
		catch (Exception $exception) 
		{
			$this->handle_exception($exception);
		}
	}

	public function handle_exception(Exception $exception)
	{
		System_Daemon::log(System_Daemon::LOG_ERR, strtr("File: :file(:line): :message", array(
			":file" => $exception->getFile(),
			":line" => $exception->getLine(),
			":message" => $exception->getMessage()
		)));
	}

	abstract public function execute($job_data);

	function __destruct()
	{
		if ($this->_refresh_database)
		{
			Database::instance()->disconnect();	
		}
	}


	static public function factory($tube_name)
	{
		$class_name = "Service_Beanstalkd_Tube_".ucfirst($tube_name);

		return new $class_name($tube_name);
	}
}