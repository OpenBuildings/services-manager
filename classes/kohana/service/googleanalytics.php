<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Google analytics service adapter
 * requires 'api-key' configuration
 * 
 * @package    OpenBuildings/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 OpenBuildings Inc.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Service_Googleanalytics extends Service implements Service_Type_Javascript
{
	public $api_key;
	public $header;

	public function init()
	{
		$this->api_key = Arr::get($this->_config, 'api-key');
		$this->header = Arr::get($this->_config, 'header');
	}

	public function header()
	{
		if ( ! $this->initialized())
			return NULL;

		return $this->header;
	}

	/**
	 * Render the required code
	 * @return string
	 */
	public function code()
	{
		if ( ! $this->initialized())
			return NULL;

		return <<<ANALYTICS
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '{$this->api_key}']);
	_gaq.push(['_trackPageview']);
	(function() {
	  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>
ANALYTICS;
	}

	public function head()
	{
		return $this->header() ? $this->code() : NULL;
	}

	public function body()
	{
		return $this->header() ? NULL : $this->code();
	}
}