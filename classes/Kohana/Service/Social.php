<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Addthis service adapter
 * requires 'api-key' configuration
 * 
 * @package    Despark/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 Despark Ltd.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Service_Social extends Service implements Service_Type_Javascript
{
	protected $_libraries = array();
	
	public function libraries($key = NULL, $value = NULL)
	{
		if ($key === NULL)
			return $this->_libraries;
	
		if (is_array($key))
		{
			$this->_libraries = $key;
		}
		else
		{
			if ($value === NULL)
				return Arr::get($this->_libraries, $key);
	
			$this->_libraries[$key] = $value;
		}
	
		return $this;
	}

	public function library($library_name)
	{
		if (($this->libraries($library_name) AND $this->config("{$library_name}.load")) OR $this->config("{$library_name}.load") === 'always')
			return $this->config("{$library_name}.lib");
	}

	public static function html5_data_attributes($options)
	{
		$data = array();
		foreach ($options as $key => $option) 
		{
			$data['data-'.$key] = $option;
		}
		return $data;
	}
	
	public function init()
	{

	}

	public function toolbox($url, array $attributes = array())
	{
		return Service_Social_Toolbox::factory($this, $url, $attributes);
	}
	
	public function pinit($url, $image, array $options = array())
	{
		if ( ! $this->initialized())
			return NULL;

		$this->libraries('pinterest', TRUE);
		
		$options = array_filter(Arr::merge(array(
			'data-layout' => 'horizontal',
			'data-url' => $url,
			'data-image' => $image,
			'class' => 'pin-it-button',
		), Service_Social::html5_data_attributes($options)));

		return '<span class="social-item social-pinit"><div '.HTML::attributes($options).'></div></span>';
	}

	public function title($url, $title, array $options = array())
	{
		$attributes = Arr::merge(array('class' => 'social-item social-item-title'), $options);

		return '<span '.HTML::attributes($attributes).'>'.$title.'</span>';
	}

	public function email($url, array $options = array())
	{
		$class = Arr::get($options, 'class', '');
		unset($options['class']);

		$query = http_build_query(array_filter(array(
			'subject' => Arr::get($options, 'subject'),
			'cc' => Arr::get($options, 'cc'),
			'bcc' => Arr::get($options, 'bcc'),
			'body' => $url,
		)));

		return '<span class="social-item social-email '.$class.'">'.HTML::anchor('mailto://'.Arr::get($options, 'email').'?'.$query, Arr::get($options, 'title', 'Share via email')).'</span>';
	}

	public function fblike($url, array $options = array())
	{
		$this->libraries('facebook', TRUE);

		$class = Arr::get($options, 'class', '');
		unset($options['class']);

		$options = array_filter(Arr::merge(array(
			'href' => $url,
			'send' => 'false',
			'layout' => 'button_count',
			'show_faces' => 'false',
			'action' => 'like',
		), $options));

		return '<span class="social-item social-fblike '.$class.'"><fb:like '.HTML::attributes($options).'></fb:like></span>';
	}

	public function twitter($url, array $options = array())
	{
		$this->libraries('twitter', TRUE);

		$class = Arr::get($options, 'class', '');
		unset($options['class']);

		$options = array_filter(Arr::merge(array(
			'class' => 'inactive-twitter-share-button',
			'data-url' => $url,
			'data-count' => 'none',
		), Service_Social::html5_data_attributes($options)));

		return '<span class="social-item social-twitter '.$class.'"><a href="https://twitter.com/share" '.HTML::attributes($options).'></a></span>';
	}

	public function head()
	{
		if ( ! $this->initialized())
			return NULL;

		$options = array();
		$render = '';

		if ($this->library('pinterest'))
		{
			$options[] = 'window.___pincfg = '.json_encode($this->config('pinterest.options'));
		}
		
		if ($options)
		{
			$render .= "<script type=\"text/javascript\">\n".join("\n", $options).'</script>';
		}
		
		return $render;
	}

	public function body()
	{
		if ( ! $this->initialized())
			return NULL;

		if ( ! $this->config('load'))
			return '';

		$libraries = array();
		$options = array(
			Service::render_file(Kohana::find_file('web/js', 'social', 'js'))
		);

		if ($this->config('analytics'))
		{
			$options[] = Service::render_file(Kohana::find_file('web/js', 'social-analytics', 'js'));
		}

		if ($library = $this->library('pinterest'))
		{
			$libraries[] = $library;
		}

		if ($library = $this->library('twitter'))
		{
			$libraries[] = $library;
		}

		$render = join("\n", array_map('HTML::script', $libraries));
		$render .= "<script type=\"text/javascript\">\n".join("\n", $options).'</script>';
		
		return $render;
	}
}