<?php
set_time_limit (0);

include_once('config.php');
require('include/insim.class.php');
include('include/functions.inc.php');

if (!defined('_REVISION')) {
	if (file_exists('.svn' . DIRECTORY_SEPARATOR. 'entries')) {
		$svn = file('.svn' . DIRECTORY_SEPARATOR . 'entries');
		if (is_numeric(trim($svn[3]))) {
			$version = $svn[3];
		} else { // pre 1.4 svn used xml for this file
			$version = explode('"', $svn[4]);
			$version = $version[1];
		}
		define ('_REVISION', trim($version));
		unset ($svn);
		unset ($version);
	} else {
		define ('_REVISION', 0); // default if no svn data avilable
	}
}

echo 'Connecting to InSim '.INSIM_SERVER.':'.INSIM_PORT.PHP_EOL;

$insim = new InSim();
$insim->debug(1);

$insim->isi(INSIM_SERVER, INSIM_PORT, INSIM_PASS);
echo 'InSim connected!'.PHP_EOL;

echo 'Connecting to IRC Server '.SERVER.PHP_EOL;
$conn = fsockopen(SERVER, 6667);
echo 'Connected'.PHP_EOL;

sendCommand("USER ".USERNAME." 0 0 ".USERNAME, $conn, false);
sendCommand("NICK ".USERNAME, $conn);
$connected = false;
$time = time();
$packet = null;

if($insim->receiver == null){
	die('InSim FAIL');
}

stream_set_blocking($conn, 0);

$foo = NULL;
$timeout = time() + 1;
$activityTimeout = time() + 30;
$resultLfs = true;
while (!feof($conn)) {
	$result = fread($conn, 1024);
	$packet = null;
	$timeout = time() + 1;
	if($serverActive) {
		$resultLfs = @socket_recv($insim->receiver, $packet, 1024, MSG_WAITALL);
	} else {
		$resultLfs = @socket_recv($insim->receiver, $packet, 1024, MSG_NOWAIT);
	}
	if(time() > $activityTimeout){
		$activityTimeout = time()+30;
		echo "Checking server idle status via LFSWorld..".PHP_EOL;
		$statFile = file_get_contents("http://www.lfsworld.net/hoststatus/?h=".urlencode(LFSHOST));
		if(strpos($statFile, '0 / ')!==false){
			$serverActive = false;
			echo "Server currently idle...".PHP_EOL;
		} else {
			$serverActive = true;
			echo "Server seems to be active...".PHP_EOL;
		}
	}
	$part = explode(" ",$result);
	if($result != ''){
		echo $result.PHP_EOL;
	}

	if(strpos($result, 'PING :')!==false)
	{
		$ping = explode(":", $result);
		$reply = explode("\n", $ping[1]);
		$reply = $reply[0];
		sendCommand("PONG $reply\n\r", $conn);
		$pong = true;
	}
	if ($pong && !$firstrun && strpos($result, USERNAME.' +i')!==false) {
		sendCommand("AUTH ".USERNAME." ".PASSWORD, $conn);
		sendCommand("JOIN ".CHANNEL."\n\r", $conn);
		sendCommand("TOPIC ".CHANNEL." :Octrin Racing - Users online on server: ".intval($insim->numConnections - 1), $conn);
		sendMessage('**** Octrin LFS/IRC Bot starting its work.. - Revion .'._REVISION.' ****', CHANNEL, $conn);
		sendMessage('**** Get ready to rumble! ****', CHANNEL, $conn);
		$firstrun = true;
	}

	if ($packet && $packet[1] == pack("C", ISP_STA)) {
		echo "Received state pack..".PHP_EOL;
		$insim->handleStatePackage($packet);

		if($insim->numConnections==1){
			$serverActive = false;
			echo "Server currently idle..".PHP_EOL;
		}
		
		if($firstrun) {
			sendCommand("TOPIC ".CHANNEL." :Octrin Racing - Users online on server: ".intval($insim->numConnections - 1), $conn);
		}
	}  else if($packet && $packet[1] == pack("C", ISP_MSO)) {
		echo "Received ISP_MSO...".PHP_EOL;

		$type_raw = substr($packet, 8, 128);
		if($previous != $type_raw){
			$previous = $type_raw;
			$text = $type_raw;
			$text = preg_replace("/\^.{1}/", '', $text);
			if(strpos($text, '!irc')){
				$text = str_replace('!irc ', '', $text);
				sendMessage('[LFS] '.$text, CHANNEL, $conn);
			}
		}
			
	}  else if($packet && $packet[1] == pack("C", ISP_PLL)) {
		echo "Received ISP_PLL...".PHP_EOL;

		if($insim->numConnections==2){
			$serverActive = false;
			echo "Last player left, server now idle..".PHP_EOL;
		}
			
	} else if($packet && $packet[1] == pack("C", ISP_TINY)) {
		echo "Received IS_TINY, replying..".PHP_EOL;
		$insim->sendTiny($insim->makeTiny(TINY_NONE));
	}else if($packet){
		//        echo strlen($packet).PHP_EOL;
	}
	$packet = null;

	// Work with incoming commands
	if(strpos($result, "PRIVMSG ".USERNAME)!==false || strpos($result, "PRIVMSG ".CHANNEL.' :!lfs')!==false){
		
		echo "Processing command..".PHP_EOL;

		$result = str_replace(array("\n","\r"), '', $result);

		$split = explode(':', $result);
		$command = $split[2];
		$op = explode('!', $split[1]);
		$op = $op[0];
		$command = explode(' ', $command);
		if(strtolower($command[0])=='!lfs'){
			$command[0] = 'lfs';
		}
		switch(strtolower($command[0])){
			case 'createcombo':
				if($command[1] == ''){
					sendMessage('No password given!', $op, $conn);
					break;
				}
				if($command[2] == ''){
					sendMessage('No date param given!', $op, $conn);
					break;
				}
				$serverResult = file_get_contents('http://www.gjl-network.net/randomlfs/random.php?date='.$command[2].'&password='.$command[1]);
				$serverResult = explode("\n", $serverResult);
				$pre = false;
				foreach($serverResult as $resultLine){
					if($resultLine == '</pre>'){
						break;
					}
					if(!$pre && $resultLine!='<pre>') {
						continue;
					} else if($resultLine == '<pre>'){
						$pre = true;
						continue;
					}
					if($resultLine=='')continue;
					sendMessage($resultLine, CHANNEL, $conn);
					$insim->sendTextMessage('[IRC]: '.$resultLine);
					if($count%4==0) {
						time_nanosleep(0, 500000000);
					}
				}
				break;
			case 'rebuildcombo':
				if($command[1] == ''){
					sendMessage('No password given!', $op, $conn);
					break;
				}
				if($command[2] == ''){
					sendMessage('No date param given!', $op, $conn);
					break;
				}
				$serverResult = file_get_contents('http://www.gjl-network.net/randomlfs/random.php?date='.$command[2].'&password='.$command[1].'&reset');
				$serverResult = explode("\n", $serverResult);
				$pre = false;
				foreach($serverResult as $count => $resultLine){
					if($resultLine == '</pre>'){
						break;
					}
					if(!$pre && $resultLine!='<pre>') {
						continue;
					} else if($resultLine == '<pre>'){
						$pre = true;
						continue;
					}
					if($resultLine=='')continue;
					sendMessage($resultLine, CHANNEL, $conn);
					$insim->sendTextMessage('[IRC]: '.$resultLine);
					if($count%4==0) {
						time_nanosleep(0, 500000000);
					}
				}
				break;
			case 'showcombo':
				if($command[1] == ''){
					sendMessage('No date param given!', $op, $conn);
					break;
				}
				$serverResult = file_get_contents('http://www.gjl-network.net/randomlfs/random.php?date='.$command[1]);
				$serverResult = explode("\n", $serverResult);
				$pre = false;
				foreach($serverResult as $count => $resultLine){
					if($resultLine == '</pre>'){
						break;
					}
					if(!$pre && $resultLine!='<pre>') {
						continue;
					} else if($resultLine == '<pre>'){
						$pre = true;
						continue;
					}
					if($resultLine=='')continue;
					sendMessage($resultLine, $op, $conn);
					if($count%4==0) {
						time_nanosleep(0, 500000000);
					}
				}
				break;
			case 'showcurrent':
				$insim->sendTiny($insim->makeTiny(TINY_SST));
				$output = 'Host: ' . formatHostname($insim->hostname) . ', LFS product: ' . $insim->lfsProduct . ', LFS version: ' . $insim->lfsVersion . ', InSim version: ' . $insim->inSimVersion;
				sendMessage($output, $op, $conn);
				$output = 'Num. Racers: ' . $insim->numRacers . ', Num. Connections: ' . $insim->numConnections;
				sendMessage($output, $op, $conn);
				switch($insim->raceInProgress) {
					case 0:
						$raceInProgress = 'idle';
						break;
					case 1:
						$raceInProgress = 'race';
						break;
					case 2:
						$raceInProgress = 'qualifying';
						break;
				}
				$output = 'Race status: ' . $raceInProgress . ', Qualifying: ' . $insim->qualMins . ' minutes, Race length: ' . $insim->raceLaps . ' ' . $insim->raceCurrency;
				sendMessage($output, $op, $conn);
				$output = 'Track: ' . getTrackName($insim->track) . ', Weather: ' . $insim->weather . ', Wind: ' . $insim->wind;
				sendMessage($output, $op, $conn);

				break;
			case 'lfs':
				if($command[1]==''){
					sendMessage('No text given!', $op, $conn);
					break;
				}
				var_dump($command[1]);
				$count = 1;
				$text = str_replace('lfs ', '', $split[2], $count);
				if(count($split)>3){
					$text = '';
					for($i=2;$i < count($split);$i++){
						$text .= ':'.$split[$i];
					}
					$text = str_replace(array(':lfs ', ':!lfs '), '', $text, $count);
				}
				sendMessage($op.' [toLFS]: '.$text, CHANNEL, $conn);
				$insim->sendTextMessage($op.'[IRC]: '.$text);
				break;
			case 'quit':
				if($command[1]!=PASSWORD){
					sendMessage('Wrong password given!', $op, $conn);
					break;
				}
				sendCommand('QUIT', $conn);
				$insim->sendTextMessage($op.'[IRC]: '.$text);
				break;
			case 'help':
				if($command[1] != '' && $command[1] != 'help'){
					switch(strtolower($command[1])){
						case 'showcurrent':
							sendMessage('Shows the current server status', $op, $conn);
							sendMessage('SHOWCURRENT - Usage: /msg '.USERNAME.' SHOWCURRENT', $op, $conn);
							break;
						case 'createcombo':
							sendMessage('Creates a new combo for the given date', $op, $conn);
							sendMessage('CREATECOMBO - Usage: /msg '.USERNAME.' CREATECOMBO <password> <date>', $op, $conn);
							break;
						case 'rebuildcombo':
							sendMessage('Resets the combo for the given date and creates a new one', $op, $conn);
							sendMessage('REBUILDCOMBO - Usage: /msg '.USERNAME.' REBUILDCOMBO <password> <date>', $op, $conn);
							break;
						case 'showcombo':
							sendMessage('Shows the combo for the given date', $op, $conn);
							sendMessage('SHOWCOMBO - Usage: /msg '.USERNAME.' SHOWCOMBO <date>', $op, $conn);
							break;
						case 'lfs':
							sendMessage('Sends a text message to LFS', $op, $conn);
							sendMessage('LFS - Usage: /msg '.USERNAME.' LFS <text>', $op, $conn);
							sendMessage('Alias: !lfs <text>', $op, $conn);
							break;
						default:
							sendMessage('Unknown command "'.$command[1].'"!', $op, $conn);
							break;
					}
				} else {
					sendMessage('Octrin LFS/IRC Bot Revision .'._REVISION, $op, $conn);
					sendMessage('Available commands: HELP, CREATECOMBO, REBUILDCOMBO, SHOWCOMBO, SHOWCURRENT, LFS', $op, $conn);
					sendMessage('Execute HELP <command> to get help on a specific command', $op, $conn);
					sendMessage('Syntax for combo commands: /msg '.USERNAME.' <command> [[<password>] <date>]', $op, $conn);
					time_nanosleep(0, 500000000);
					sendMessage('Sample: /msg '.USERNAME.' CREATECOMBO secretpassword TestEvent', $op, $conn);
					sendMessage('Sample: /msg '.USERNAME.' SHOWCOMBO TestEvent', $op, $conn);
				}
				break;
			default:
				sendMessage('Unknown command "'.$command[0].'"!', $op, $conn);
		}
	}
}

$insim->disconnect();

function sendMessage($input, $receiver = CHANNEL, $conn){
	sendCommand('PRIVMSG '.$receiver.' :'.$input, $conn);
}

function sendCommand($input, $conn, $noRead = true){
	echo $input.PHP_EOL;
	fwrite($conn, $input."\n\r");
	if(!$noRead) {
		$return = fread($conn,1024);
		echo $return.PHP_EOL;
	}
	return $return;
}
?>
