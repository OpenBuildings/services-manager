<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Chartbeat service adapter
 * requires 'config' configuration with uid and domain keys
 * 
 * @package    OpenBuildings/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 OpenBuildings Inc.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Service_Chartbeat extends Service implements Service_Type_Javascript
{
	/**
	 * This will be added to _sf_async_config variable in the script
	 * @var array
	 */
	public $_config = array();

	/**
	 * Set api_key and addthis_config, and load current user email if logged
	 * @return NULL
	 */
	public function init()
	{
		$this->_config = Arr::get($this->_config, 'config');
	}

	/**
	 * Change or retrieve configuration
	 * @param  array|string $config
	 * @return array|string|Kohana_Service_Chartbeat
	 */
	public function config($config = NULL)
	{
		if (is_array($config))
		{
			$this->_config = Arr::merge($this->_config, $config);
			return $this;
		}
		elseif (is_string($config))
		{
			return Arr::get($this->_config, $config);
		}

		return $this->_config;
	}

	public function head()
	{
		return '<script type="text/javascript">var _sf_startpt=(new Date()).getTime()</script>';
	}

	/**
	 * Render the javascript for addthis and render the script tag with the addthis_config variable
	 * @return string
	 */
	public function body()
	{
		if ( ! $this->initialized())
			return NULL;
		
		$config = json_encode((object) $this->_config);

		return <<<ANALYTICS
		<script type="text/javascript">
		var _sf_async_config={$config};
		(function(){
		  function loadChartbeat() {
		    window._sf_endpt=(new Date()).getTime();
		    var e = document.createElement('script');
		    e.setAttribute('language', 'javascript');
		    e.setAttribute('type', 'text/javascript');
		    e.setAttribute('src',
		       (("https:" == document.location.protocol) ? "https://a248.e.akamai.net/chartbeat.download.akamai.com/102508/" : "http://static.chartbeat.com/") +
		       "js/chartbeat.js");
		    document.body.appendChild(e);
		  }
		  var oldonload = window.onload;
		  window.onload = (typeof window.onload != 'function') ?
		     loadChartbeat : function() { oldonload(); loadChartbeat(); };
		})();
		</script>
ANALYTICS;
	}
}