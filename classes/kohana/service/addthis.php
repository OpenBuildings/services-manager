<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Addthis service adapter
 * requires 'api-key' configuration
 * 
 * @package    OpenBuildings/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 OpenBuildings Inc.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Service_Addthis extends Service implements Service_Type_Javascript
{
	/**
	 * The api key used for addthis, something like ra-4f023b426eb7e111
	 * @var string
	 */
	public $api_key;

	/**
	 * This will be added to addthis_config in the script
	 * @var array
	 */
	public $addthis_config = array();

	/**
	 * Load the current user email to be used in the addthis config, boolean
	 * @var bool
	 */
	public $user_email = FALSE;

	/**
	 * Set api_key and addthis_config, and load current user email if logged
	 * @return NULL
	 */
	public function init()
	{
		$this->api_key = Arr::get($this->_config, 'api-key');
		$this->addthis_config = Arr::get($this->_config, 'addthis-config');

		if (Arr::get($this->_config, 'load-user-email') AND Auth::instance()->logged_in())
		{
			$this->user_email = Auth::instance()->get_user()->email;
		}
	}

	public function head()
	{
		return NULL;
	}

	/**
	 * Render an addthis toolbox
	 * @param  string $url        Override the current request url
	 * @param  array $attributes add custom attributes to the div, you can set 'class' => 'yourclass' and the default classes will still be added
	 * @return string HTML div with the box
	 */
	public function toolbox($url = NULL, $attributes = NULL)
	{
		if ( ! $this->initialized())
			return NULL;

		$attributes = (array) $attributes;
		$attributes['addthis:url'] = $url ? $url : URL::site(Request::initial()->url(), TRUE);
		$attributes['class'] = Arr::get($attributes, 'class').' social-box addthis_toolbox addthis_default_style';

		$attrs = HTML::attributes($attributes);
		return "
			<div $attrs>
				<a class=\"addthis_button_preferred_1\"></a>
				<a class=\"addthis_button_preferred_2\"></a>
				<a class=\"addthis_button_preferred_3\"></a>
				<a class=\"addthis_button_compact\">Share</a>
			</div>";
	}

	/**
	 * Render the javascript for addthis and render the script tag with the addthis_config variable
	 * @return string
	 */
	public function body()
	{
		if ( ! $this->initialized())
			return NULL;
		
		$addthis_config = json_encode((object) $this->addthis_config);

		$render = <<<ANALYTICS
  	<script type="text/javascript" src="http://s7.addthis.com/js/300/addthis_widget.js#pubid={$this->api_key}"></script>
  	<script type="text/javascript">
  		var addthis_config = {$addthis_config};
  	</script>
ANALYTICS;

		return strtr($render, array(':user-email' => $this->user_email)); 
	}
}