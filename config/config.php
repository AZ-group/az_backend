<?php

require_once 'constants.php';
require_once HELPERS_PATH. 'etc.php';

// puede afectar el punto decimal al formar sentencias SQL !!!
// setlocale(LC_ALL, 'es_AR.UTF-8');

return [
	'APP_URL' => env('APP_URL'),

	#
	# For a sub-foder in /var/www/html just set as
	# BASE_URL' => /folder/'
	#
	'BASE_URL' => '/',   

	'ROUTER' => true,
	'FRONT_CONTROLLER' => true,
	
	/*
		urls start with /api/ if REMOVE_API_SLUG is set to false
	*/	
	'REMOVE_API_SLUG' => false, 
	'HTTPS' => 'Off',
	'DEFAULT_CONTROLLER' => 'LoginController',

	'db_connections' => [
		'db1' => [
			'host'		=> env('DB_HOST', '127.0.0.1'),
			'port'		=> env('DB_PORT', 3306),
			'driver' 	=> env('DB_CONNECTION'),
			'db_name' 	=> env('DB_DATABASE'), 
			'user'		=> env('DB_USERNAME'), 
			'pass'		=> env('DB_PASSWORD'),
			'pdo_options' => [
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_EMULATE_PREPARES => false
			]
		],
		
		'db2' => [
			'host'		=> env('DB_HOST_2', '127.0.0.1'),
			'port'		=> env('DB_PORT_2', 3306),
			'driver' 	=> env('DB_CONNECTION_2'),
			'db_name' 	=> env('DB_DATABASE_2'), 
			'user'		=> env('DB_USERNAME_2'), 
			'pass'		=> env('DB_PASSWORD_2'),
		],

		'db3' => [
			'host'		=> env('DB_HOST_3', '127.0.0.1'),
			'port'		=> env('DB_PORT_3', 3306),
			'driver' 	=> env('DB_CONNECTION_3'),
			'db_name' 	=> env('DB_DATABASE_3'), 
			'user'		=> env('DB_USERNAME_3'), 
			'pass'		=> env('DB_PASSWORD_3'),
			'pdo_options' => [
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_EMULATE_PREPARES => false
			]
		],

		'db4' => [
			'host'		=> env('DB_HOST_4', '127.0.0.1'),
			'port'		=> env('DB_PORT_4', 3306),
			'driver' 	=> env('DB_CONNECTION_4'),
			'db_name' 	=> env('DB_DATABASE_4'), 
			'user'		=> env('DB_USERNAME_4'), 
			'pass'		=> env('DB_PASSWORD_4'),
			'pdo_options' => [
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_EMULATE_PREPARES => false
			]
		],

		'db5' => [
			'host'		=> env('DB_HOST_5', '127.0.0.1'),
			'port'		=> env('DB_PORT_5', 3306),
			'driver' 	=> env('DB_CONNECTION_5'),
			'db_name' 	=> env('DB_DATABASE_5'), 
			'user'		=> env('DB_USERNAME_5'), 
			'pass'		=> env('DB_PASSWORD_5'),
			'pdo_options' => [
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_EMULATE_PREPARES => false
			]
		],
		'db6' => [
			'host'		=> env('DB_HOST_6', '127.0.0.1'),
			'port'		=> env('DB_PORT_6'),
			'driver' 	=> env('DB_CONNECTION_6'),
			'db_name' 	=> env('DB_DATABASE_6'), 
			'user'		=> env('DB_USERNAME_6'), 
			'pass'		=> env('DB_PASSWORD_6'),
			'pdo_options' => [
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_EMULATE_PREPARES => false
			]
		]	

	], 

	'db_connection_default' => 'db1',  // cambiar de nuevo a db1
	
	'DateTimeZone' => 'America/Argentina/Buenos_Aires',

	'error_handling'   => false,
	'debug'   => env('APP_DEBUG', true),

	'access_token' => [
		'secret_key' 		=> env('TOKENS_ACCSS_SECRET_KEY'),
		'expiration_time'	=> 60 * 15 * 10000,   // seconds (normalmente 60 * 15)
		'encryption'		=> 'HS256'			
	],

	'refresh_token' => [
		'secret_key'		=> env('TOKENS_REFSH_SECRET_KEY'),
		'expiration_time' 	=> 315360000,   // seconds
		'encryption' 		=> 'HS256'	
	],

	'method_override' => [
		'by_url' => true,
		'by_header' => true
	],

	/* 
		Any role listed bellow if it is asked then will be auto-aproved.
	*/
	'auto_approval_roles' => ['basic', 'regular'],

	/*
		If you need email confirmation then pre_activated should be false
	*/
	'pre_activated' => false,

	'email' => [
		'secret_key' => env('TOKENS_EMAIL_SECRET_KEY'),
		'expires_in' => 7 * 24 * 3600,
		'encryption' => 'HS256',
		'mailer' =>  [	
			'from'	 => [env('MAIL_DEFAULT_FROM_ADDR'), env('MAIL_DEFAULT_FROM_NAME')],	
			'object' => [
				'Host'			=> env('MAIL_HOST'),
				'Port'			=> env('MAIL_PORT'),
				'Username' 		=> env('MAIL_USERNAME'),
				'Password' 		=> env('MAIL_PASSWORD'),
				'SMTPSecure'	=> env('MAIL_ENCRYPTION'),
	            'SMTPAuth' 		=> true,
				'SMTPDebug' 	=> 4,
				'CharSet' 		=> 'UTF-8',
				'Debugutput' 	=> 'html',
				'SMTPSecure'	=> false
			]
		]
	],

	'pretty' => false,	
	
	'paginator' => [
					'max_limit' => 50,
					'default_limit' => 10,
					'position' => 'BOTTOM'
	],

	'google_auth'  => [
		'client_id' 	=> env('OAUTH_GOOGLE_CLIENT_ID'),
		'client_secret' => env('OAUTH_GOOGLE_CLIENT_SECRET'),
		'callback_url' 	=> env('OAUTH_GOOGLE_CALLBACK')
	],

	'facebook_auth' => [
		'app_id' 		=> env('OAUTH_FACEBOOK_CLIENT_ID'),
		'app_secret'	=> env('OAUTH_FACEBOOK_CLIENT_SECRET'), 
		'callback_url'	=> env('OAUTH_FACEBOOK_CALLBACK')
	],

	/*
		Service Providers
	*/

	'providers' => [
		devdojo\calculator\CalculatorServiceProvider::class,
		boctulus\grained_acl\GrainedAclServiceProvider::class,
		//boctulus\basic_acl\BasicAclServiceProvider::class
		// ...
	],
	
];