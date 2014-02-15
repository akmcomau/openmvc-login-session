<?php
$_MODULE = [
	"name" => "Login Sessions",
	"description" => "Support for tracking and adding restrictions to login sessions",
	"namespace" => "\\modules\\login_session",
	"config_controller" => "administrator\\LoginSessions",
	"controllers" => [
		"administrator\\LoginSessions"
	],
	"hooks" => [
		"authentication" => [
			"init_authentication" => "classes\\Hooks",
			"after_loginCustomer" => "classes\\Hooks",
			"after_loginAdministrator" => "classes\\Hooks",
			"after_logoutCustomer" => "classes\\Hooks",
			"after_logoutAdministrator" => "classes\\Hooks"
		]
	],
	"default_config" => [
		"time_to_live" => 3600,
		"admin_concurrency" => 1,
		"customer_concurrency" => 1,
	]
];

