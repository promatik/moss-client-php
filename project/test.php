<?php
require_once('moss.php');

new MossTest();

class MossTest
{
	public $moss;
	
	function __construct()
	{
		$this->moss = new MOSS("127.0.0.1", 30480, "TestUser", "TestRoom");
		
		echo "connect: " . (int)$this->moss->connect() . "\r\n";
		
		echo "getUser: " . (int)$this->moss->getUser("1", "1")['online'] . "\r\n";
		echo "getUsers: " . var_dump($this->moss->getUsers("1")) . "\r\n";
		echo "getUserCount: " . $this->moss->getUserCount("1") . "\r\n";
		
		echo "invoke: " . (int)$this->moss->invoke("1", "1", "update", "weekly") . "\r\n";
		echo "updateStatus: " . $this->moss->updateStatus("1") . "\r\n";
		echo "invokeOnAll: " . (int)$this->moss->invokeOnAll("update", "weekly") . "\r\n";
	}
};

?>