<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Service_Social_Toolbox {

	public static function factory(Service_Social $service, $url, array $attributes = array())
	{
		return new Service_Social_Toolbox($service, $url, $attributes);
	}

	protected $_service;
	protected $_template = '<div :attributes>:widgets</div>';
	protected $_url;
	protected $_attributes = array('class' => 'social-toolbox');
	
	public function attributes($key = NULL, $value = NULL)
	{
		if ($key === NULL)
			return $this->_attributes;
	
		if (is_array($key))
		{
			$this->_attributes = $key;
		}
		else
		{
			if ($value === NULL)
				return Arr::get($this->_attributes, $key);
	
			$this->_attributes[$key] = $value;
		}
	
		return $this;
	}
	
	public function url($url = NULL)
	{
		if ($url !== NULL)
		{
			$this->_url = $url;
			return $this;
		}
		return $this->_url;
	}
	
	public function template($template = NULL)
	{
		if ($template !== NULL)
		{
			$this->_template = $template;
			return $this;
		}
		return $this->_template;
	}

	public function __construct(Service_Social $service, $url, array $attributes = array())
	{
		$this->_service = $service;
		$this->_url = $url;
		$this->_attributes = Arr::merge($this->_attributes, $attributes);
	}

	public function render()
	{
		if ( ! $this->_service->initialized())
			return NULL;

		$widgets = array();
		foreach ($this->_widgets as $name => $widget_args) 
		{
			array_unshift($widget_args, $this->url());
			$widgets[] = call_user_func_array(array($this->_service, $name), $widget_args);
		}
		
		return strtr($this->template(), array(':attributes' => HTML::attributes($this->attributes()), ':widgets' => join("\n", $widgets)));
	}

	public function __toString()
	{
		return (string) $this->render();
	}

	public function email(array $options = array())
	{
		$this->_widgets['email'] = func_get_args();
		return $this;
	}

	public function twitter(array $options = array())
	{
		$this->_widgets['twitter'] = func_get_args();
		return $this;
	}

	public function fblike(array $options = array())
	{
		$this->_widgets['fblike'] = func_get_args();
		return $this;
	}

	public function pinit($image, array $options = array())
	{
		$this->_widgets['pinit'] = func_get_args();
		return $this;
	}

	public function title($title, array $options = array())
	{
		$this->_widgets['title'] = func_get_args();
		return $this;
	}
}