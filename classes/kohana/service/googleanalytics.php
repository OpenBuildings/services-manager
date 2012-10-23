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
	protected $_events_queue = array();
	protected $_custom_vars = array();


	public function init()
	{
	}

	public function queue()
	{
		return $this->_events_queue;
	}

	public function custom_vars()
	{
		return $this->_custom_vars;
	}

	/**
	 * This method accepts four parameters:
   *
 	 * index  — 	 The slot for the custom variable. Required. 
 	 * 				 		 This is a number whose value can range from 1 - 5, inclusive. 
 	 * 				 		 A custom variable should be placed in one slot only and not be re-used across different slots.
 	 * 				 		 
 	 * name   — 	 The name for the custom variable. Required. 
 	 * 				 		 This is a string that identifies the custom variable and appears in the top-level 
 	 * 				 		 Custom Variables report of the Analytics reports.
 	 * 				 		 
   * value  — 	 The value for the custom variable. Required. 
   * 				 		 This is a string that is paired with a name. 
   * 				 		 You can pair a number of values with a custom variable name. 
   * 				 		 The value appears in the table list of the UI for a selected variable name. 
   * 				 		 Typically, you will have two or more values for a given name. 
   * 				 		 For example, you might define a custom variable name gender and supply male and female as two possible values.
   * 				 
 	 * opt_scope — The scope for the custom variable. Optional. 
 	 * 						 As described above, the scope defines the level of user engagement with your site. 
 	 * 						 It is a number whose possible values are 1 (visitor-level), 2 (session-level), 
 	 * 						 or 3 (page-level). When left undefined, the custom variable scope defaults to page-level interaction.
 	 * 						 
	 * @param int $index
	 * @param string $name
	 * @param string $value
	 * @param int $opt_scope
	 */
	public function set_custom_var($index, $name, $value, $opt_scope = 3)
	{
		$this->_custom_vars[$index] = array(
			'index' => $index,
			'name' => $name,
			'value' => $value,
			'opt_scope' => $opt_scope
		);
	}

	public function track_event($category, $action, $label = NULL, $value = NULL, $non_interaction = NULL)
	{
    $this->_events_queue[] = array(
    	'category' => $category, 
    	'action' => $action, 
    	'label' => $label, 
    	'value' => $value, 
    	'non_interaction' => $non_interaction
    );
	}


	/**
	 * Render the required code
	 * @return string
	 */
	public function code()
	{
		if ( ! $this->initialized() OR ! $api_key = $this->config('api-key'))
			return NULL;

		$events = $this->events_code();
		$custom_vars = $this->custom_vars_code();

		return <<<ANALYTICS
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '{$api_key}']);
	{$events}
	{$custom_vars}
	_gaq.push(['_trackPageview']);
	(function() {
	  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>
ANALYTICS;
	}

	protected function events_code()
	{
		$events = NULL;

		foreach ($this->queue() as $event) 
  	{
  		$events .= "_gaq.push(['_trackEvent', \"{$event['category']}\", \"{$event['action']}\", \"{$event['label']}\", \"{$event['value']}\", \"{$event['non_interaction']}\"]);\n";				
  	}

  	return $events;
	}

	protected function custom_vars_code()
	{
		$vars = NULL;

		foreach ($this->custom_vars() as $index => $properties)
		{
			$vars .= "_gaq.push(['_setCustomVar', {$index}, '{$properties['name']}', \"{$properties['value']}\", \"{$properties['opt_scope']}\"]);\n";
		}

		return $vars;
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