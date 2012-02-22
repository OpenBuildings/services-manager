<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Interface for javascript interfaces must be renderable
 * 
 * @package    OpenBuildings/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 OpenBuildings Inc.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
interface Service_Type_Javascript
{
	/**
	 * Render Javascript script tag, at the end of the body tag
	 * @return string
	 */
	public function body();

	/**
	 * Render some javascript for the header tag if its needed
	 * @return string
	 */
	public function head();
}