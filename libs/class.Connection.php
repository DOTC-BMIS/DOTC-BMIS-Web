<?php
require_once( 'config.php' );

class PDO_Connection extends PDO_Connection_Config{
	protected $conn;
	
	public function __construct(){
		$this->_connect();
	}
	
	public function PDO_Connection(){
		# do nothing
	}
	
	private function _connect(){
		$this->conn = new PDO( 'mysql:host=' . $this->server_details['server'] . ';dbname=' . $this->server_details['dbname'] . ';charset=utf8', $this->server_details['username'], $this->server_details['password'] );
		$this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$this->conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
	}
	
	public function debug( $arr = array() ){
		echo "<pre>";
		print_r( $arr );
		echo "</pre>";
	}
}
?>