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
	'database'         => MODPATH.'database',
	'auth'             => MODPATH.'auth',
	'jam'              => __DIR__.'/../modules/jam',
	'jam-auth'         => __DIR__.'/../modules/jam-auth',
	'services-manager' => __DIR__.'/..',
));

Kohana::$config
	->load('database')
		->set('default', array(
			'type'       => 'PDO',
			'connection' => array(
                'dsn' => 'mysql:host=localhost;dbname=test-services-manager',
				'username'   => 'root',
				'password'   => '',
				'persistent' => TRUE,
			),
            'identifier' => '`',
			'table_prefix' => '',
			'charset'      => 'utf8',
			'caching'      => FALSE,
		));

Kohana::$config
	->load('auth')
		->set('session_key', 'auth_user')
		->set('hash_key', '11111');

Kohana::$environment = Kohana::TESTING;
