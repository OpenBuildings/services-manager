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
	public $_permissions;
	public $_meta = array();

	public function init()
	{
		$this->_meta['fb:app_id'] = Arr::path($this->_config, 'auth.appId');
		if ($admins = Arr::get($this->_config, 'admins'))
		{
			$this->_meta['fb:admins'] = $admins;
		}
	}

	public function og_post($action, $name, $url, array $params = array())
	{
		if ( ! $this->initialized() OR ! $this->og_enabled())
			return NULL;

		if ( ! Valid::url($url))
			throw new Kohana_Exception("URL :url passed to facebook is not valid", array(':url' => $url));

		if ($this->og_namespace())
		{
			$action = $this->og_namespace().':'.$action;
		}

		return $this->api("/me/{$action}", 'POST', Arr::merge(array($name => $url), array_filter($params)));
	}

	public function delete($opengraph_id)
	{
		return $this->api('/'.$opengraph_id, 'DELETE');
	}

	/**
	 * Perform a post request with the facebook API
	 * @param  string $message [description]
	 * @param  string $name    [description]
	 * @param  string $url     [description]
	 * @param  string $picture [description]
	 * @return mixed
	 */
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

	/**
	 * Get the facebook permissions, authorized by the user OR check for a single permission or an array of permissions
	 * @param  string|array $permission check for those permissions
	 * @return bool|array
	 */
	public function permissions($permission = NULL)
	{
		if ( ! $this->initialized())
			return NULL;

		if ( ! $this->_permissions)
		{
			$this->_permissions = array_keys(Arr::path($this->api('/me/permissions'), 'data.0', array()));
		}

		if ($permission !== NULL)
		{
			return count(array_diff((array) $permission, $this->_permissions)) == 0;
		}

		return $this->_permissions;
	}


	/**
	 * Get the data from a graph /me request
	 * @return array
	 */
	public function user_data()
	{
		if ( ! $this->initialized())
			return NULL;

		if ( ! $this->_user_data)
		{
			$this->_user_data = $this->api('/me');
		}

		return $this->_user_data;
	}

	/**
	 * Get or set the access token for the current facebook session
	 * @param  string $access_token [description]
	 * @return string
	 */
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

	/**
	 * Get the facebook id of the user for the current session
	 * @return string
	 */
	public function user_id()
	{
		if ( ! $this->initialized())
			return NULL;

		if ( ! $this->_user_id)
		{
			$this->_user_id = $this->facebook()->getUser();
		}

		return $this->_user_id;
	}

	/**
	 * Check if the facebook session needs to be refresshed (expired access token)
	 * @return bool
	 */
	public function needs_refresh()
	{
		return ! (bool) $this->facebook()->getUser();
	}

	/**
	 * Perform an API request on facebook, wrapper method on facebook php-sdk api method
	 * @param  string $request the request URL
	 * @return mixed
	 */
	public function api($request)
	{
		if ( ! $this->initialized())
			return NULL;

		$args = func_get_args();

		return call_user_func_array(array($this->facebook(), 'api'), $args);
	}

	/**
	 * Return the facebook php-sdk
	 * @return Facebook
	 */
	public function facebook()
	{
		if ( ! $this->initialized())
			return NULL;

		if ($this->_facebook)
			return $this->_facebook;

		require_once Kohana::find_file('vendor/facebook-sdk', 'facebook');

		return $this->_facebook = new Facebook(Arr::get($this->_config, 'auth'));
	}

	/**
	 * Get the og namespage from configuration
	 * @return string
	 */
	public function og_namespace()
	{
		if ( ! $this->initialized())
			return NULL;

		return Arr::get($this->_config, 'namespace', FALSE);
	}

	/**
	 * Get / set meta tags for facebook. You can pass an array, or a key and value arguments.
	 * Also if you want multiple values for each key, just use an array value
	 * @param  string|array $value  a key or a key => value array
	 * @param  miexed $value2 the value to be set
	 * @return array|NULL
	 */
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
		$options = json_encode(Arr::merge(array('appId' => Arr::path($this->_config, 'auth.appId')), Arr::get($this->_config, 'jssdk', array())));

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