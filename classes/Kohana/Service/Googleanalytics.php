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
abstract class Kohana_Service_Googleanalytics extends Service implements Service_Type_Javascript {

	const SCOPE_VISITOR = 1;

	const SCOPE_SESSION = 2;

	const SCOPE_PAGE = 3;

	protected $_access_token;

	protected $_events_queue = array();

	protected $_custom_vars = array();

	protected $_transaction = array();

	protected $_items = array();

	protected $_currency;

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
	 * @param integer index  — The slot for the custom variable. Required. 
	 * This is a number whose value can range from 1 - 5, inclusive. 
	 * A custom variable should be placed in one slot only and not be re-used across different slots.
	 *           
	 * @param string $name   — The name for the custom variable. Required. 
	 * This is a string that identifies the custom variable and appears in the top-level 
	 * Custom Variables report of the Analytics reports.
	 *
	 * @param string $value  — The value for the custom variable. Required. 
	 * This is a string that is paired with a name. 
	 * You can pair a number of values with a custom variable name. 
	 * The value appears in the table list of the UI for a selected variable name. 
	 * Typically, you will have two or more values for a given name. 
	 * For example, you might define a custom variable name gender and supply male and female as two possible values.
	 *
	 * @param integer $opt_scope — The scope for the custom variable. Optional. 
	 * As described above, the scope defines the level of user engagement with your site. 
	 * It is a number whose possible values are 1 (visitor-level), 2 (session-level), 
	 * or 3 (page-level). When left undefined, the custom variable scope defaults to page-level interaction.
	 */
	public function set_custom_var($index, $name, $value, $opt_scope = self::SCOPE_PAGE)
	{
		if ( ! $this->initialized())
			return NULL;

		$this->_custom_vars[$index] = array(
			'index' => $index,
			'name' => $name,
			'value' => $value,
			'opt_scope' => $opt_scope
		);
	}

	public function set_currency($currency)
	{
		if ( ! $this->initialized())
			return NULL;

		$this->_currency = $currency;
	}

	public function set_transaciton(array $transaction)
	{
		if ( ! $this->initialized())
			return NULL;

		$this->_transaction = $transaction;
	}

	public function set_items(array $items)
	{
		if ( ! $this->initialized())
			return NULL;

		$this->_items = $items;
	}

	public function track_event($category, $action, $label = NULL, $value = NULL, $opt_noninteraction = NULL)
	{
		if ( ! $this->initialized())
			return NULL;

		$event_data = array(
			'category' => $category, 
			'action' => $action
		);

		if ($label !== NULL)
		{
			$event_data['label'] = (string) $label;
		}

		if ($value !== NULL)
		{
			$event_data['value'] = (int) $value;
		}

		if ($opt_noninteraction !== NULL)
		{
			$event_data['opt_noninteraction'] = ((bool) $opt_noninteraction) ? 'true':'false';
		}

		$this->_events_queue[] = $event_data;
	}

	/**
	 * Render the required code
	 * @return string
	 */
	public function code()
	{
		if ( ! $this->initialized() OR ! ($api_key = $this->config('api-key')))
			return NULL;

		$events = $this->events_code();
		$custom_vars = $this->custom_vars_code();
		$ecommerce = $this->ecommerce_code();
		$currency = $this->currency_code();

		return <<<ANALYTICS
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '{$api_key}']);
	_gaq.push(['_trackPageview']);
	{$custom_vars}
	{$events}
	{$currency}
	{$ecommerce}
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
			$event_keys = array('category', 'action', 'label', 'value', 'opt_noninteraction');
			$params = join(', ', array_filter(Arr::extract($event, $event_keys)));

			$events .= "_gaq.push(['_trackEvent', {$params}]);\n";
		}

		return $events;
	}

	protected function currency_code()
	{
		$code = NULL;

		if ($this->_currency) 
		{
			$code .= "_gaq.push(['_set', 'currencyCode', '{$this->_currency}']);\n";
		}

		return $code;
	}

	protected function ecommerce_code()
	{
		$code = NULL;

		if ($this->_transaction AND $this->_items) 
		{
			$transaction_code = array_merge(array('_addTrans'), $this->_transaction);

			$code .= "_gaq.push(".json_encode($transaction_code).");\n";

			foreach ($this->_items as $item)
			{
				$item_code = array_merge(array('_addItem'), $item);
				$code .= "_gaq.push(".json_encode($item_code).");\n";
			}

			$code .= "_gaq.push(['_trackTrans']);\n";
		}

		return $code;
	}

	protected function custom_vars_code()
	{
		$vars = NULL;

		foreach ($this->custom_vars() as $index => $properties)
		{
			$vars .= "_gaq.push(['_setCustomVar', {$index}, '{$properties['name']}', \"{$properties['value']}\", {$properties['opt_scope']}]);\n";
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