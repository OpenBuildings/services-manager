<?php

abstract class Testcase_Extended extends PHPUnit_Framework_TestCase {

	// public $environment;
	
	public function setUp()
	{
		parent::setUp();
		// Database::instance()->begin();
		// Jam_Association_Creator::current(1);

		// $this->env = new EB\Environment(array(
		// 	'static' => new EB\Environment_Group_Static(),
		// 	'config' => new EB\Environment_Group_Config(),
		// ));
	}

	public function tearDown()
	{
		// Database::instance()->rollback();	
		
		// $this->env->restore();

		parent::tearDown();
	}
}