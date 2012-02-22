<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Kissinsights service adapter
 * requires 'api-key' configuration
 * 
 * @package    OpenBuildings/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 OpenBuildings Inc.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Service_Kissinsights extends Service implements Service_Type_Javascript
{
	public $api_file;

	public function init()
	{
		$this->api_file = $this->_config['api-file'];
	}

	public function head()
	{
		if ( ! $this->initialized())
			return NULL;

		return <<<ANALYTICS
  	<script type="text/javascript">var _kiq = _kiq || [];</script>
  	<script type="text/javascript" src="{$this->api_file}" async="true"></script>
ANALYTICS;
	}

	public function body()
	{
		return NULL;
	}
}