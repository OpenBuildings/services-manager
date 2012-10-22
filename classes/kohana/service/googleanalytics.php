<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Google analytics service adapter
 * requires 'api-key' configuration
 * 
 * @package    Despark/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 Despark Ltd.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Service_Googleanalytics extends Service implements Service_Type_Javascript
{
	protected $_access_token;

	public function init()
	{
	}


	/**
	 * Render the required code
	 * @return string
	 */
	public function code()
	{
		if ( ! $this->initialized() OR ! $api_key = $this->config('api-key'))
			return NULL;

		return <<<ANALYTICS
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '{$api_key}']);
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
		return $this->config('in_header') ? $this->code() : NULL;
	}

	public function body()
	{
		return $this->config('in_header') ? NULL : $this->code();
	}
}