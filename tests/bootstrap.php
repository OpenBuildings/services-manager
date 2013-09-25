<?php 

spl_autoload_register(function($class)
{
	$file = __DIR__.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.str_replace('_', '/', $class).'.php';

	if (is_file($file))
	{
		require_once $file;
	}
});

require_once __DIR__.'/../vendor/autoload.php';

Kohana::modules(array(
	// 'database'        => MODPATH.'database',
	// 'auth'            => MODPATH.'auth',
	// 'REQUIRED MODULE' => __DIR__.'/../modules/REQUIRED MODULE',
	'template-module' => __DIR__.'/..',
));

// Kohana::$config
// 	->load('database')
// 		->set('default', array(
// 			'type'       => 'MySQL',
// 			'connection' => array(
// 				'hostname'   => 'localhost',
// 				'database'   => 'test-MODULE-NAME',
// 				'username'   => 'root',
// 				'password'   => '',
// 				'persistent' => TRUE,
// 			),
// 			'table_prefix' => '',
// 			'charset'      => 'utf8',
// 			'caching'      => FALSE,
// 		));

// Kohana::$config
// 	->load('auth')
// 		->set('session_key', 'auth_user')
// 		->set('hash_key', '11111');

Kohana::$environment = Kohana::TESTING;