<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Facebook service adapter
 * requires 'auth' configuration array with apiId and secret
 * 
 * @package    OpenBuildings/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 OpenBuildings Inc.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Service_Facebook extends Service implements Service_Type_Php, Service_Type_Javascript
{
	public $_facebook;
	public $_user_id;
	public $_user_data;
	public $_meta = array();

	public function init()
	{
		$this->_meta['fb:app_id'] = Arr::path($this->_config, 'auth.appId');
		if ($admins = Arr::get($this->_config, 'admins'))
		{
			$this->_meta['fb:admins'] = $admins;
		}
	}

	public function og_post($action, $name, $url)
	{
		if ( ! $this->initialized() OR ! $this->og_enabled())
			return NULL;

		if ( ! Valid::url($url))
			throw new Kohana_Exception("URL :url passed to facebook is not valid", array(':url' => $url));

		if ($this->og_namespace())
		{
			$action = $this->og_namespace().':'.$action;
		}

		$og_post = "https://graph.facebook.com/me/{$action}?".http_build_query(array(
			$name => $url,
			'access_token' => $this->access_token(),
		));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $og_post);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}

	public function feed_post($message, $name, $url, $picture = NULL)
	{
		if ( ! $this->initialized())
			return NULL;
		$defaults = Arr::get($this->_config, 'post', array());

		$attachment = Arr::merge($defaults, array(
			'message' => $message,
			'name' => $name,
			'link' => $url,
			'picture' => $picture ? $picture : Arr::get($defaults, 'picture')
		));
		
		$this->api('/me/feed', 'POST', $attachment);
	}

	public function og_enabled()
	{
		if ( ! $this->initialized())
			return NULL;

		return Arr::get($this->_config, 'og_enabled', FALSE);
	}

	protected function _get_permissions()
	{
		$permissions = $this->facebook()->api('/me/permissions');
		return isset($permissions['data'][0]) ? array_keys($permissions['data'][0]) : array();
	}

	/**
	 * Get all persmissons OR check for a single permission or an array of permissions
	 * @param string|array $permission 
	 * @return bool|array
	 */
	public function permissions($permission = NULL)
	{
		if ( ! $this->initialized())
			return NULL;

		if ($permission === NULL)
		{
			return $this->_get_permissions();
		}
		if (is_string($permission))
		{
			return in_array($permission, $this->_get_permissions());
		}
		if (is_array($permission))
		{
			$array_diff = array_diff($permission, $this->_get_permissions());
			return empty($array_diff);
		}
	}

	public function user_data()
	{
		if ( ! $this->initialized())
			return NULL;

		if ($this->_user_data)
			return $this->_user_data;
		
		return $this->_user_data = $this->facebook()->api('/me');
	}	

	public function access_token($access_token = NULL)
	{
		if ( ! $this->initialized())
			return NULL;

		if ($access_token !== NULL)
		{
			$this->facebook()->setAccessToken($access_token);
			return $this;
		}

		return $this->facebook()->getAccessToken();
	}

	public function user_id()
	{
		if ( ! $this->initialized())
			return NULL;

		if ($this->_user_id)
			return $this->_user_id;

		return $this->_user_id = $this->facebook()->getUser();
	}

	public function needs_refresh()
	{
		return ! (bool) $this->facebook()->getUser();
	}

	public function api($request)
	{
		if ( ! $this->initialized())
			return NULL;

		$args = func_get_args();

		return call_user_func_array(array($this->facebook(), 'api'), $args);
	}

	public function facebook()
	{
		if ( ! $this->initialized())
			return NULL;

		if ($this->_facebook)
			return $this->_facebook;

		require_once Kohana::find_file('vendor/facebook-sdk', 'facebook');

		return $this->_facebook = new Facebook(Arr::get($this->_config, 'auth'));
	}

	public function og_namespace()
	{
		if ( ! $this->initialized())
			return NULL;

		return Arr::get($this->_config, 'namespace', FALSE);
	}

	public function meta($value = NULL, $value2 = NULL)
	{
		if ( ! $this->initialized())
			return NULL;

		if ($value2 !== NULL)
		{
			$value = array($value => $value2);
		}

		if ($value !== NULL)
		{
			$this->_meta = Arr::merge($this->_meta, (array) $value);
			return $this;
		}

		return $this->_meta;
	}

	public function head()
	{
		$tags = array();
		foreach ($this->_meta as $key => $value) 
		{
			$key = $this->_meta_params($key);

			if (is_array($value))
			{
				foreach ($value as $value_item) 
				{
					$tags[] = '<meta property="'.$key.'" content="'.$this->_meta_params($value_item).'" />';
				}
			}
			else
			{
				$tags[] = '<meta property="'.$key.'" content="'.$this->_meta_params($value).'" />';
			}
		}
		return join("\n", $tags);
	}

	protected function _meta_params($value)
	{
		return strtr($value, array('{namespace}' => $this->og_namespace()));
	}

	public function body()
	{
		$options = json_encode(Arr::merge(array('appId' => Arr::path($this->_config, 'auth.appId')), Arr::get($this->_config, 'jssdk')));

		return <<<BODY
			<div id="fb-root"></div>
			<script>
				window.fbAsyncInit = function() { FB.init($options);};
				(function(d){
				  var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
				  js = d.createElement('script'); js.id = id; js.async = true;
				  js.src = '//connect.facebook.net/en_US/all.js';
				  d.getElementsByTagName('head')[0].appendChild(js);
				}(window.document));
			</script>
BODY;
	}
}