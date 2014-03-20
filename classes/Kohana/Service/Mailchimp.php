<?php defined('SYSPATH') OR die('No direct script access.');

use Openbuildings\Swiftmailer\GoogleCampaignPlugin;

/**
 * Exceptionalio service adapter
 * requires 'api-key' configuration
 *
 * @package    Despark/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 Despark Ltd.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Service_Mailchimp extends Service implements Service_Type_Php
{
	public $_api;

	protected $_mapped_methods = array(
		'listsSubscribe',
		'listsUnsubscribe',
		'listsMemberInfo',
		'listsUpdateMember',
		'campaignsSchedule',
		'campaignsUnschedule',
		'campaignsSendTest',
		'campaignsSend',
		'campaignsCreate',
		'campaignsUpdate',
		'helperInlineCss',
		'helperListsForEmail'
	);

	public function api()
	{
		if ( ! $this->initialized())
			return NULL;

		return $this->_api;
	}

	public function __get($name)
	{
		if ( ! $this->initialized())
			return NULL;

		if (property_exists($this, $name))
		{
			return $this->$name;
		}
		else
		{
			return $this->api()->$name;
		}
	}

	public function __call($method, $args)
	{
		if ( ! $this->initialized())
			return NULL;

		$callee = array($this, $method);

		if (in_array($method, $this->_mapped_methods))
		{
			$parts = preg_split('/(?=[A-Z])/', $method);
			$section = $parts[0];
			array_shift($parts);

			if ($section == 'lists')
			{
				$args[0] = Arr::path($this->_config, 'lists.'.$args[0], $args[0]);
			}

			$parts[0] = lcfirst($parts[0]);
			$method = join('', $parts);

			$callee = array($this->$section, $method);
		}

		$return = call_user_func_array($callee, $args);

		return $return;
	}

	public function process_html($html, $strip_css = FALSE, $campaigns = array())
	{
		if (count($campaigns))
		{
			$main_campaign = reset($campaigns);
			array_shift($campaigns);
			$html = GoogleCampaignPlugin::embedCampaigns($html, $main_campaign, $campaigns, 'UTF-8');
		}

		$html = $this->helperInlineCss($html, $strip_css);

		return $html['html'];
	}

	/**
	 * Run Exceptional setup
	 * @return NULL
	 */
	public function init()
	{
		$this->api_key = $this->_config['api-key'];
		$this->_api = new Mailchimp($this->_config['api-key']);
	}

}
