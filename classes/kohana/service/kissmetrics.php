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
	public $api_key;
	public $php_api;
	public $more = '';
	public $queue = array();

	/**
	 * KM::record wrapper, silantly fails if Kissmetrics is not enabled
	 * @param  string $action
	 * @param  array  $props
	 * @return NULL
	 */
	public function record($action, $props = array())
	{
		if ($this->initialized() AND $this->php_api)
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
		if ($this->initialized() AND $this->php_api)
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
		if ($this->initialized() AND $this->php_api)
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
		$this->api_key = Arr::get($this->_config, 'api-key');
		$this->php_api = Arr::get($this->_config, 'php-api');
		$this->more = Arr::get($this->_config, 'more');

		if ($this->php_api)
		{
			require_once Kohana::find_file('vendor', 'km');

			KM::init($this->api_key);

			if (isset($_COOKIE['km_ni']))
			{
				// Visitor has a JS set named kiss metrics identity.
				KM::identify($_COOKIE['km_ni']);
			}
			elseif (isset($_COOKIE['km_ai']))
			{
				// Visitor has a JS set named kiss metrics identity.
				KM::identify($_COOKIE['km_ai']);
			}
		}
	}

	/**
	 * Render the head script tags
	 * @return string
	 */
	public function head()
	{
		return NULL;
	}

	public function notifications_for_user()
	{
		if ($role = Arr::get($this->_config, 'notifications-for-role'))
		{
			return Auth::instance()->logged_in($role);
		}
		
		return FALSE;
	}

	/**
	 * Render the body tags (EMPTY)
	 * @return NULL
	 */
	public function body()
	{
		if ( ! $this->initialized())
			return NULL;

		if (Arr::get($this->_config, 'use-auth') AND Auth::instance()->logged_in())
		{
			if ( ! isset($_COOKIE['km_ni']))
			{
				$this->queue[] = array('identify', Auth::instance()->get_user()->email);
			}
		}
		$more = $this->render_queue($this->queue);

		if ($this->more)
		{
			$more .= "\n".View::factory($this->more);
		}

		if ($this->notifications_for_user())
		{
			return <<<ANALYTICS_NOTIFICATIONS
			<script type="text/javascript">
				var _kmq = _kmq || [];
				if (window.webkitNotifications && window.jQuery)
				{
					window.jQuery('<div style="position:fixed; z-index:100000; bottom: 5px; left: 5px; width: 40px; height: 25px;"><button class="cl-button small default">KM '+(sessionStorage.getItem("_km_notifications") || 'off')+'</button>')
						.appendTo('body')
						.find('button')
							.click(function(){
								if (sessionStorage.getItem("_km_notifications") === 'on')
								{
									sessionStorage.setItem("_km_notifications", 'off');
								}
								else
								{
									window.webkitNotifications.requestPermission();
									sessionStorage.setItem("_km_notifications", 'on');
								}
								window.jQuery(this).text('KM '+sessionStorage.getItem("_km_notifications"));
							});

					var kissmetrics_notification = function(text) {
						if (sessionStorage.getItem("_km_notifications") === 'on')
						{
							var notification = window.webkitNotifications.createNotification('http://www.quickonlinetips.com/archives/wp-content/uploads/kissmetrics-logo.png', 'Kissmetrics Event', text);
							notification.show();
						}
					}

					_kmq.push = function(item) {
						if (item[0] === 'trackClick' || item[0] === 'trackClickOnOutboundLink') {
							$('body').on('click', item[1].charAt(0) === '.' ? item[1] : '#' + item[1], function(event){
								kissmetrics_notification(item[2])
							});
						}
						else if(item[0] === 'record')
						{
							kissmetrics_notification(item[1])
						}
						return Array.prototype.push.apply(this, arguments);
					}
				}

				{$more}
			</script>
ANALYTICS_NOTIFICATIONS;
		}
		else
		{
			return <<< ANALYTICS
			<script type="text/javascript">
				var _kmq = _kmq || [];

				function _kms(u){
				  setTimeout(function(){
				  var s = document.createElement('script'); var f = document.getElementsByTagName('script')[0]; s.type = 'text/javascript'; s.async = true;
				  s.src = u; f.parentNode.insertBefore(s, f);
				  }, 1);
				}

				_kms('//i.kissmetrics.com/i.js');
				_kms('//doug1izaerwt3.cloudfront.net/{$this->api_key}.1.js');

				{$more}
			</script>
ANALYTICS;
		}
	}
}