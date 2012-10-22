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
	const SCOPE_VISITOR = 1;
	const SCOPE_SESSION = 2;
	const SCOPE_PAGE = 3;

	protected $_access_token;
	protected $_custom_vars = array();
	
	public function set_custom_var($index, $name, $value, $opt_scope = Service_Googleanalytics::SCOPE_PAGE)
	{
		$this->_custom_vars[] = array($index, $name, $value, $opt_scope);
		return $this;
	}

	public function init()
	{
	}


	/**
	 * Render the required code
	 * @return string
	 */
	public function code()
	{
		if ( ! $this->initialized())
			return NULL;

		$custom_vars = '';
		foreach ($this->_custom_vars as $var) 
		{
			$custom_vars .= "_gaq.push(['_setCustomVar', {$var[0]}, {$var[1]}, {$var[2]}, {$var[3]}]);\n";
		}

		return <<<ANALYTICS
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '{$api_key}']);
	_gaq.push(['_trackPageview']);
	{$custom_vars}
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