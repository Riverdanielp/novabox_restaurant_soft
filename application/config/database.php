<?php defined('BASEPATH') OR exit('No direct script access allowed');

// $base = "irestoraplus";
// $base = "restaurant-soft_victorias";
// $base = "restaurant-soft_lomitosycia";
// $base = "restaurant-soft_miabuela2";
$base = "restaurant-soft_market-miabuela";
$base2 = "restaurant-soft_miabuela";

$db_username = 'root';
$db_password = '';

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',
	'username' => $db_username,
	'password' => $db_password,
	'database' => $base,
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => FALSE,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8mb4',
	'dbcollat' => 'utf8mb4_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => FALSE
);

$db['db2'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',
	'username' => $db_username,
	'password' => $db_password,
	'database' => $base2,
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => FALSE,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8mb4',
	'dbcollat' => 'utf8mb4_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => FALSE
);
