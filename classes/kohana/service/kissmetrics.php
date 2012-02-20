<?php defined('SYSPATH') OR die('No direct script access.');

/**
* Kissmetrics service adapter
* requires 'api-key' configuration
*/
abstract class Kohana_Service_Kissmetrics extends Service implements Service_Type_Javascript, Service_Type_Php
{
	public $api_key;
	public $queue = array();

	public function queue($queue)
	{
		if ( ! $this->initialized())
			return NULL;

		$queue = func_get_args();
		
		if ($this->is_async())
		{
			return '<script type="text/javascript">'.$this->render_queue($queue).'</script>';
		}
		else
		{
			$this->queue += $queue;
		}

	}

	public function is_async()
	{
		return Request::current()->is_ajax();
	}

	public function render_queue($queue)
	{
		$queue_js = '';
		foreach ($queue as $event) 
		{
			$queue_js .= "_kmq.push(".json_encode($event).");\n";
		}
		return $queue_js;
	}

	public function init()
	{
		$this->api_key = $this->_config['api-key'];

		if ($this->_config['php-api'])
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

	public function header()
	{
		if ( ! $this->initialized())
			return NULL;


		if ($this->_config['use-auth'] AND Auth::instance()->logged_in() AND ! isset($_COOKIE['km_ni']))
		{
			$this->queue[] = array('identify', Auth::instance()->get_user()->email);
		}

		$more = $this->render_queue($this->queue);

		return <<<ANALYTICS
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

	public function body()
	{
		return NULL;
	}
}