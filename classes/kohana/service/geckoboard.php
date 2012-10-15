<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Geckoboard widgets
 * 
 * @package    Despark/services-manager
 * @author     Ivan Kerin
 * @copyright  (c) 2012 Despark Ltd.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Service_Geckoboard extends Service implements Service_Type_Php
{
	public function init()
	{
	}

	public function number($number, $relative_number = NULL)
	{
		if ( ! $this->initialized())
			return NULL;

		$items []= array(
			'value' => $number,
			'text' => "",
		);
		
		$items []= array(
			'value' => $relative_number,
			'text' => "",
		);
		
		# ~ 
		
		$data = array(
			 'item' => $items,
		);
		
		return json_encode($data);
	}

	public function text($text)
	{
		$data = array(
			'item' => array(
				array(
				   'text' => $text,
				   'type' => 0,
				),
			),
		);
		
		return json_encode($data);
	}

	public function texts(array $texts)
	{
		$items = array();
		
		foreach ($texts as $text)
		{
			$items []= array(
				'text' => $text,
				'type' => 0,
			);
		}
		
		# ~ 
		
		$data = array(
			 'item' => $items,
		);
		
		return json_encode($data);
	}
}