<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Web Application',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
        'application.extensions.PHPMailer.PHPMailer'
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'root',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
            'newFileMode'=>0664,
            'newDirMode'=>0755,
		),
	),

	// application components
	'components'=>array(

		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),

		// uncomment the following to enable URLs in path-format
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),

        'urlFormat'=>'path',
        'rules'=>array(
            'gii'=>'gii',
            'gii/<controller:\w+>'=>'gii/<controller>',
            'gii/<controller:\w+>/<action:\w+>'=>'gii/<controller>/<action>'
        ),

		// database settings are configured in database.php
		'db'=>require(dirname(__FILE__).'/database.php'),

		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>YII_DEBUG ? null : 'site/error',
		),

		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),

	),
	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
        'base_url' => 'http://api.betskeez.com/',
		'adminEmail' => 'webmaster@example.com',
        'db_prefix' => 'skeez_',
        'php_mailer' => array(
            'from_header_email' => 'no-reply@betskeez.com',
            'from_header_name' => 'SkeezBet Admin'
        ),
        //Email template
        'emailTemplates' => array(
            'welcome' => array(
                'subject' => 'Thanks you for joining BetSkeez',
                'template' => 'registration_welcome'
            ),
            'forgot' => array(
                'subject' => 'You requested to reset password',
                'template' => 'forgot_password'
            ),
            'email_friend' => array(
                    'subject' => 'You got a friend request',
                    'template' => 'email_friend_notification'
                ),
            'approve_email_friend' => array(
                'subject' => 'Your friend request had been approved',
                'template' => 'approve_email_friend_notification'
            ),
            'decline_email_friend' => array(
                'subject' => 'Your friend request had been rejected',
                'template' => 'decline_email_friend_notification'
            ),
            'bets_email_friend' => array(
                'subject' => 'You have been invited to a bet',
                'template' => 'bets_email_friend_notification'
            ),
            'approve_bets_email_friend' => array(
                'subject' => 'Your bet had been approved',
                'template' => 'approve_bets_email_friend_notification'
            ),
            'decline_bets_email_friend' => array(
                'subject' => 'Your bet had been declined',
                'template' => 'decline_bets_email_friend_notification'
            )
        )
    )
);
