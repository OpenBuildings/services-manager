<?php defined('SYSPATH') OR die('No direct script access.');

/**
* Interface for javascript interfaces must be renderable
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
	public function header();
}