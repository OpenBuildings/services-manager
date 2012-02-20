<?php defined('SYSPATH') OR die('No direct script access.');

/**
* Addthis service adapter
* requires 'api-key' configuration
*/
abstract class Kohana_Service_Addthis extends Service implements Service_Type_Javascript
{
	public $api_key;
	public $addthis_config = array();
	public $user_email;

	public function init()
	{
		$this->api_key = $this->_config['api-key'];
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