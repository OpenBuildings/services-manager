<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Kissmetrics service adapter
 * requires 'api-key' configuration
 *
 * @package    Despark/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 Despark Ltd.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Service_Kissmetrics extends Service implements Service_Type_Javascript, Service_Type_Php
{
	public $queue = array();

	/**
	 * KM::record wrapper, silantly fails if Kissmetrics is not enabled
	 * @param  string $action
	 * @param  array  $props
	 * @return NULL
	 */
	public function record($action, $props = array())
	{
		if ($this->initialized() AND $this->config('php-api'))
		{
			KM::record($action, $props);
		}

		return $this;
	}

	/**
	 * KM::record wrapper, silantly fails if Kissmetrics is not enabled
	 * @param  array $params_array
	 * @return NULL
	 */
	public function set($params_array)
	{
		if ($this->initialized() AND $this->config('php-api'))
		{
			KM::set($params_array);
		}

		return $this;
	}

	/**
	 * KM::record wrapper, silantly fails if Kissmetrics is not enabled
	 * @param  string $identifier
	 * @return NULL
	 */
	public function identify($identifier)
	{
		if ($this->initialized() AND $this->config('php-api'))
		{
			KM::identify($identifier);
		}

		return $this;
	}

	/**
	 * Add event to record queue. If its an ajax request - return a script tag with the events inside it, otherwise add them to the queue to display in the header.
	 * @param  array $queue event
	 * @param  array ...
	 * @return string
	 */
	public function queue($queue = NULL)
	{
		if ( ! $this->initialized())
			return NULL;

		if ($queue === NULL)
		{
			return $this->queue;
		}

		$queue = func_get_args();

		if ($this->is_async())
		{
			return '<script type="text/javascript">'.$this->render_queue($queue).'</script>';
		}
		else
		{
			$this->queue = array_merge($this->queue, $queue);
		}

	}

	/**
	 * Return if its async environment or not.
	 * @return boolean
	 */
	public function is_async()
	{
		return Request::initial()->is_ajax();
	}

	/**
	 * Render an event queue. Return javascript commands to add thoes events to the kissmetrics queue
	 * @param  array $queue the queue to render.
	 * @return string
	 */
	public function render_queue(array $queue)
	{
		$queue_js = '';
		foreach ($queue as $event)
		{
			$queue_js .= "_kmq.push(".json_encode($event).");\n";
		}
		return $queue_js;
	}

	/**
	 * Initialize, if php-api is enabled, load KM class and identify the user based on km_ni/km_ai cookie
	 * @return NULL
	 */
	public function init()
	{
		if ($this->config('php-api'))
		{
			require_once Kohana::find_file('vendor', 'km');

			KM::init($this->config('api-key'));

			if ($ids = array_filter(Arr::extract($_COOKIE, array('km_ni', 'km_ai'))))
			{
				KM::identify(reset($ids));
			}
		}
	}

	/**
	 * Render the head script tags (EMPTY)
	 * @return string
	 */
	public function head()
	{
		return NULL;
	}

	/**
	 * Extend the normal is_enabled_for_user method to allow showing notifications even for roles
	 * that have kissmetrics disabled
	 * @return bool 
	 */
	public function is_enabled_for_user()
	{	
		return ($this->has_notifications_for_user() OR parent::is_enabled_for_user());
	}

	/**
	 * See if the current user has notifications enabled for him
	 * @return bool
	 */
	public function has_notifications_for_user()
	{
		if ($role = $this->config('notifications-for-role'))
		{
			return Auth::instance()->logged_in($role);
		}
		
		return FALSE;
	}

	/**
	 * Render the body tags
	 * @return NULL
	 */
	public function body()
	{
		if ( ! $this->initialized())
			return NULL;

		if ($this->config('use-auth') AND Auth::instance()->logged_in())
		{
			if ( ! isset($_COOKIE['km_ni']))
			{
				$this->queue[] = array('identify', Auth::instance()->get_user()->email);
			}
		}
		$events_queue = $this->render_queue($this->queue);

		if ($more = $this->config('more'))
		{
			$events_queue .= "\n".View::factory($more);
		}

		if ($this->has_notifications_for_user())
		{
			$js = Service::render_file(Kohana::find_file('web/js', 'kissmetrics-notifications', 'js'));
		}
		else
		{
			$js = Service::render_file(Kohana::find_file('web/js', 'kissmetrics-init', 'js'), array('api_key' => $this->config('api-key')));	
		}

		return "<script type=\"text/javascript\">\n{$js}\n{$events_queue}\n</script>";
	}
}