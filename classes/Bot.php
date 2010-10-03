<?php

/**
 * Copyright (c) 2010 Sebastian Bauer
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Sebastian Bauer <sbauer@gjl-network.net>
 * @license MIT
 */
class Bot {

	private $_commands = array( );
	private $_responder = array( );
	private $_conn = null;
	private $_packetCount = 0;
	private $_serverIdle = true;
	private $_topic = '';

	public function setServerIdle($bool){
		$bool = (bool)$bool;
		if($bool){
			$this->log("Server currently idle..");
		} else {
			$this->log("Server currently active..");
		}
		$this->_serverIdle = $bool;
	}

	public function getTopic(){
		$topicAdd = '';
		if($this->_topic != '') {
			$topicAdd = ' - '.$this->_topic;
		}
		return $topicAdd;
	}

	public function setTopic($topic){
		$this->_topic = $topic;
	}

	public function getPacketCount(){
		return $this->_packetCount;
	}

	public function resetPacketCount(){
		$this->_packetCount = 0;
	}

	public function increasePacketCount(){
		$this->_packetCount++;
	}

	public function addInSimResponder( iInSimResponder $responder ){
		$this->_responder[strtoupper(get_class($responder))] = $responder;
	}

	public function addCommand( iCommand $command ){
		$this->_commands[strtoupper(get_class($command))] = $command;
	}

	public function getCommand($commandName){
		$commandName = strtoupper('COMMAND'.$commandName);
		if(!array_key_exists($commandName, $this->_commands)){
			return false;
		}
		return $this->_commands[$commandName];
	}

	public function getCommands(){
		return $this->_commands;
	}

	public function forgetCommands(){
		$this->_commands = array();
	}

	public function run(){
		$this->log('Connecting to InSim '.INSIM_SERVER.':'.INSIM_PORT);

		$insim = new InSim();
		$insim->debug(1);

		$insim->isi(INSIM_SERVER, INSIM_PORT, INSIM_PASS);
		$this->log('InSim connected!');

		$this->log('Connecting to IRC Server');
		$this->_conn = fsockopen(SERVER, IRC_PORT);
		$this->log('Connected');

		$this->sendCommand("USER ".USERNAME." 0 0 ".USERNAME, false);
		$this->sendCommand("NICK ".USERNAME);
		$connected = false;
		$time = time();
		$packet = null;

		if($insim->receiver == null){
			die('InSim FAIL');
		}

		stream_set_blocking($this->_conn, 0);

		$activityTimeout = time() + 30;
		$resultLfs = true;
		$packetCount = 0;

		while (!feof($this->_conn)) {
			$result = fread($this->_conn, 1024);
			$packet = null;

			if(!$this->_serverIdle) {
				$packet = socket_read($insim->receiver, 512, PHP_BINARY_READ);
//				$resultLfs = @socket_recv($insim->receiver, $packet, 1024, MSG_WAITALL);
			} else {
                $packet = socket_read($insim->receiver, 512, PHP_BINARY_READ);
//				$resultLfs = @socket_recv($insim->receiver, $packet, 1024, MSG_NOWAIT);
			}
			if(time() > $activityTimeout){
				$activityTimeout = time()+30;
				$this->log("Checking server idle status via LFSWorld..");
				$statFile = file_get_contents("http://www.lfsworld.net/hoststatus/?h=".urlencode(LFSHOST));
				if(strpos($statFile, '0 / ')!==false){
					$this->_serverIdle = true;
					$this->setIrcTopic();
					$this->log("Server currently idle...");
				} else {
					$this->_serverIdle = false;
					$this->log("Server seems to be active...");
				}
			}
			$part = explode(" ",$result);
			if($result != ''){
				$this->log($result);
			}

			if(strpos($result, 'PING :')!==false)
			{
				$matches = array( );
				preg_match("/PING \:([^\n\r]+)/", $result, $matches);
				$reply = $matches[1];
				$this->sendCommand("PONG $reply\n\r", $this->_conn);
				$pong = true;
			}
			if ($pong && !$firstrun && strpos($result, USERNAME.' +i')!==false) {
				$this->sendCommand("AUTH ".USERNAME." ".PASSWORD);
				$this->sendCommand("JOIN ".CHANNEL."\n\r");
				$this->sendMessage(' **** Beelzebot, the Octrin LFS/IRC Bot, is starting its work.. - Revion .'._REVISION.' **** ', CHANNEL);
				$this->sendMessage(' **** http://beelzebot.googlecode.com **** ', CHANNEL);
				$this->sendMessage(' **** Hell awaits! **** ', CHANNEL);
                $insim->getStatePack();
                $resultLfs = @socket_recv($insim->receiver, $packet, 1024, MSG_NOWAIT);
                if($connCount < 0) $connCount = 0;
                $this->setIrcTopic($connCount);
				$firstrun = true;
			}

            // Work with incoming commands
            if(strpos($result, "PRIVMSG ".USERNAME)!==false || strpos($result, "PRIVMSG ".CHANNEL.' :!')!==false){
                $this->log("Processing command..");
                $returnVal = null;
                foreach($this->_commands as $command){
                    $returnVal = $command->call($result, $insim, $this);
                    if($returnVal || $returnVal === false) {
                        break;
                    }
                }
                if(is_null($returnVal)){
                    $this->log('No handler for command found!');
                    $this->sendMessage('Unknown command "'.str_replace(array("\n","\r"), '', aCommand::extractCommand($result)).'"', aCommand::extractSender($result));
                }
            }

			// Work with incoming InSim Packets
			if($packet){
				foreach($this->_responder as $responder){
					$responder->call($packet, $insim, $this);
				}
			} else {
			}
		}
		$insim->disconnect();
	}

	public function sendMessage($input, $receiver = CHANNEL){
		$this->sendCommand('PRIVMSG '.$receiver.' :'.$input);
	}

    public function sendNotice($input, $receiver = CHANNEL){
        $this->sendCommand('NOTICE '.$receiver.' :'.$input);
    }

	public function sendCommand($input, $noRead = true){
		$this->log($input);
		fwrite($this->_conn, $input."\n\r");
		if(!$noRead) {
			$return = fread($this->_conn, 1024);
			$this->log($return);
		}
		return $return;
	}

	public function log($text){
		echo date("[Y-m-d h:i:s] ").$text.PHP_EOL;
	}

	public function setIrcTopic($numConns = 0){
        $this->sendCommand("TOPIC ".CHANNEL." :".sprintf(TOPIC_TEMPLATE, $numConns).$this->getTopic());
	}
}
