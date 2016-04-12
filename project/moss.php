<?php
class MOSS
{
	private $socket;
	private $host, $port, $id, $room, $status;
	public $connected;
	
	function __construct($host, $port = 30480, $id = 0, $room = "", $status = 0)
	{
		define("DELIMITER", "&!");
		$this->connected = false;
		
		$this->host = $host;
		$this->port = $port;
		$this->id = $id;
		$this->room = $room;
		$this->status = $status;
	}
	
	public function connect($id = 0, $room = "", $status = 0)
	{
		if($this->connected)
			return true;
		
		if($id != 0) $this->id = $id;
		if($room != "") $this->room = $room;
		if($status != 0) $this->status = $status;
		
		$this->socket = fsockopen($this->host, $this->port, $errnum, $errstr, 5);
		$this->call('connect', array($this->id, $this->room, $this->status));
		$result = $this->readResponse("connected");
		
		$this->connected = ($result[1] == "connected");
		return $this->connected;
	}
	
	private function readResponse($waitFor = "MOSS")
	{
		$result = $c = "";
		while(($c = fread($this->socket, 1)) !== false)
		{
			if($c == "|")
				break;
			
			$result .= $c;
		}
		preg_match('/#MOSS#<!(.+)!>#<!(.+)?!>#<!(.+)?!>#<!(.+)?!>#/', $result, $matches);
		
		return strpos($result, $waitFor) === false ? $this->readResponse($waitFor) : $matches;
	}
	
	public function disconnect()
	{
		if(!$this->connected)
			return;
		
		$this->call('disconnect');
		fclose($this->socket);
	}
	
	public function resetHost($host = null, $port = 0, $id = 0)
	{
		if(isset($host)) $this->host = $host;
		if($port > 0) $this->port = $port;
		if($id != 0) $this->id = $id;
		
		$this->disconnect();
		$this->connect();
	}

	public function call($command, $message="")
	{
		if($this->connected || $command == 'connect') {
			$message = "|#MOSS#<!" . $command . "!>#<!" . (is_array($message) ? join(DELIMITER, $message) : $message) . "!>#<!0!>#|";
			fwrite($this->socket, $message);
			fflush($this->socket);
		}
	}

	public function updateStatus($status)
	{
		$this->status = $status;
		$this->call('updateStatus', $status);
		$result = $this->readResponse("statusUpdated");
		return $result[1] == "statusUpdated";
	}
	
	public function getUser($id, $room)
	{
		$this->call('getUser', implode(DELIMITER, array($id, $room)));
		$result = $this->readResponse('user');
		$userData = explode(',', $result[3]);
		return array(
			'status' => $userData[2],
			'online' => $userData[3] == "on"
		);
	}
	
	public function getUsers($room, $limit = 20, $page = 0)
	{
		$this->call('getUsers', implode(DELIMITER, array($room, $limit, $page)));
		$result = $this->readResponse('users');
		$users = explode(DELIMITER, $result[3]);
		
		$results = array();
		foreach ($users as $user) {
			$userData = explode(',', $user);
			array_push($results, array(
				'id' => $userData[0],
				'room' => $userData[1],
				'status' => $userData[2]
			));
		}
		return $results;
	}
	
	public function getUserCount($room)
	{
		$this->call('getUsersCount', $room);
		$result = $this->readResponse('usersCount');
		return $result[3];
	}

	public function invoke($id, $room, $command, $message="")
	{
		$this->call('invoke', array($id, $room, $command, json_encode($message)));
		$result = $this->readResponse("invoke");
		return $result[3] == "ok";
	}

	public function invokeOnRoom($room, $command, $message="")
	{
		$this->call('invokeOnRoom', array($room, $command, json_encode($message)));
		$result = $this->readResponse("invokeOnRoom");
		return $result[3] == "ok";
	}

	public function invokeOnAll($command, $message="")
	{
		$this->call('invokeOnAll', array($command, json_encode($message)));
		$result = $this->readResponse("invokeOnAll");
		return $result[3] == "ok";
	}
};
?>