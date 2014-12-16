<?php
session_start();
error_reporting( E_ALL ^ E_NOTICE );
set_time_limit( 0 );
date_default_timezone_set( 'Asia/Manila' );

class PDO_Connection_Config{
	protected $server_details = array(
		'server' => '',
		'username' => '',
		'password' => '',
		'dbname' => '',
		'prefix' => ''
	);
}
?>
