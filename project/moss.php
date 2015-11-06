<?php
class MOSS
{
	private $socket;
	private $delimiter = "&!";
	public $connected;
	
	function __construct()
	{
		$this->connected = false;
	}
	
	public function connect($host, $port = 30480, $id = 0, $room = 0, $status = 0)
	{
		$this->socket = fsockopen($host, $port, $errnum, $errstr, 5);
		$this->connected = true;
		$this->call('connect', array($id, $room, $status));
		return is_resource($this->socket);
	}
	
	public function disconnect()
	{
		$this->call('disconnect');
		fclose($this->socket);
	}

	public function call($command, $message="")
	{
		if($this->connected) {
			$message = "|#MOSS#<!" . $command . "!>#<!" . (is_array($message) ? join($this->delimiter, $message) : $message) . "!>#<!0!>#|";
			fwrite($this->socket, $message);
			fflush($this->socket);
		}
	}

	public function updateStatus($status)
	{
		$this->call('updateStatus', $status);
	}

	public function invoke($id, $room, $command, $message="")
	{
		$this->call('invoke', array($id, $room, $command, json_encode($message)));
	}

	public function invokeOnRoom($room, $command, $message="")
	{
		$this->call('invokeOnRoom', array($room, $command, json_encode($message)));
	}

	public function invokeOnAll($command, $message="")
	{
		$this->call('invokeOnAll', array($command, json_encode($message)));
	}
};
?>
