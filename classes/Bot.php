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

define('INSIM_KEEPALIVE', 30);

class Bot {

	/**
	 * @var iCommand[]
	 */
	private $_commands = array( );
	/**
	 * @var iInSimResponder[]
	 */
	private $_responder = array( );
	/**
	 * @var resource
	 */
	private $_conn = null;
	/**
	 * @var int
	 */
	private $_packetCount = 0;
	/**
	 * @var bool
	 */
	private $_serverIdle = true;
	/**
	 * @var string
	 */
	private $_topic = '';

	private $ircServer = '';
	private $ircPort = 0;
	private $ircChannel = '';
	private $ircUser = '';
	private $ircPassword = '';
	private $insimServer = '';
	private $insimPort = 0;
	private $insimPassword = '';
    private $lfsHost = '';

	public function __construct($ircServer, $ircPort, $ircChannel, $ircUser, $ircPassword, $insimServer, $insimPort, $insimPassword, $lfsHost){
        $this->ircServer = $ircServer;
        $this->ircPort = $ircPort;
        $this->ircChannel = $ircChannel;
        $this->ircUser = $ircUser;
        $this->ircPassword = $ircPassword;
        $this->insimServer = $insimServer;
        $this->insimPort = $insimPort;
        $this->insimPassword = $insimPassword;
        $this->lfsHost = $lfsHost;
	}

	/**
	 * Sets the LFS server's state.
	 *
	 * @param bool $bool
	 */
	public function setServerIdle($bool){
		$bool = (bool)$bool;
		if($bool){
			$this->log("Server currently idle..");
		} else {
			$this->log("Server currently active..");
		}
		$this->_serverIdle = $bool;
	}

	/**
	 * Returns the manually set topic, not including auto-generated meta-information
	 *
	 * @return string
	 */
	public function getTopic(){
		$topicAdd = '';
		if($this->_topic != '') {
			$topicAdd = ' - '.$this->_topic;
		}
		return $topicAdd;
	}

	/**
	 * Sets the topic. This will add $topic to the auto-generated topic content.
	 * Please note: this does not include sending a TOPIC message to the IRC server!
	 *
	 * @param string $topic
	 */
	public function setTopic($topic){
		$this->_topic = $topic;
	}

    /**
     * Returns the packet count. Required for checking how many
     * packets were received during some actions.
     *
     * @return int
     */
	public function getPacketCount(){
		return $this->_packetCount;
	}

	/**
	 * Resets the packet count
	 */
	public function resetPacketCount(){
		$this->_packetCount = 0;
	}

	/**
	 * Increases the packet count by 1
	 */
	public function increasePacketCount(){
		$this->_packetCount++;
	}

    /**
     * Adds a new iInSimResponder to the responder stack
     *
     * @param iInSimResponder $responder
     */
	public function addInSimResponder( iInSimResponder $responder ){
		$this->_responder[strtoupper(get_class($responder))] = $responder;
	}

    /**
     * Adds a new iCommand command handler to the commands stack
     *
     * @param iCommand $command
     */
	public function addCommand( iCommand $command ){
		$this->_commands[strtoupper(get_class($command))] = $command;
	}

    /**
     * Returns the command given in $commandName
     *
     * @param string $commandName
     * @return iCommand
     */
	public function getCommand($commandName){
		$commandName = strtoupper('COMMAND'.$commandName);
		if(!array_key_exists($commandName, $this->_commands)){
			return false;
		}
		return $this->_commands[$commandName];
	}

    /**
     * Returns an array of all currently loaded commands
     *
     * @return iCommand[]
     */
	public function getCommands(){
		return $this->_commands;
	}

	/**
	 * Resets the commands list and empties the command stack
	 *
	 */
	public function forgetCommands(){
		$this->_commands = array();
	}

	/**
	 * Let the bot start to do its work
	 *
	 * @return string
	 */
	public function run(){
		$this->log('Connecting to InSim '.$this->insimServer.':'.$this->insimPort);

		$insim = new InSim();
		$insim->debug(1);

		$insim->isi($this->insimServer, $this->insimPort, $this->insimPassword);

		if($insim->client == FALSE || $insim->receiver == FALSE){
            $this->log('InSim connection failed!');
            $this->log($insim->errstr);
            return false;
		}
		$this->log('InSim connected!');

		$this->log('Connecting to IRC Server');
		$this->_conn = fsockopen($this->ircServer, $this->ircPort);
		$this->log('Connected');

		$this->sendCommand("USER ".$this->ircUser." 0 0 ".$this->ircUser, false);
		$this->sendCommand("NICK ".$this->ircUser);
		$connected = false;
		$time = time();
		$packet = null;

		if($insim->receiver == null){
			die('InSim FAIL');
		}

		stream_set_blocking($this->_conn, 0);

		$activityTimeout = time() + 30;
		$inSimKeepAlive = time() + INSIM_KEEPALIVE;
		$resultLfs = true;
		$packetCount = 0;

		while (!feof($this->_conn)) {
			$result = fread($this->_conn, 1024);
			$packet = null;

			if(time() > $inSimKeepAlive){
			    $insim->sendTiny($insim->makeTiny(TINY_NONE));
			    $inSimKeepAlive = time()+INSIM_KEEPALIVE;
			}

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
				$statFile = file_get_contents("http://www.lfsworld.net/hoststatus/?h=".urlencode($this->lfsHost));
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
			if ($pong && !$firstrun && strpos($result, $this->ircUser.' +i')!==false) {
				$this->sendCommand("AUTH ".$this->ircUser." ".$this->ircPassword);
				$this->sendCommand("JOIN ".$this->ircChannel."\n\r");
				$this->sendMessage(' **** Beelzebot, the Octrin LFS/IRC Bot, is starting its work.. - Revion .'._REVISION.' **** ', $this->ircChannel);
				$this->sendMessage(' **** http://beelzebot.googlecode.com **** ', $this->ircChannel);
				$this->sendMessage(' **** Hell awaits! **** ', $this->ircChannel);
                $insim->getStatePack();
                $packet = socket_read($insim->receiver, 512, PHP_BINARY_READ);
                if($connCount < 0) $connCount = 0;
                $this->setIrcTopic($connCount);
				$firstrun = true;
			}

            // Work with incoming commands
            if(strpos($result, "PRIVMSG ".$this->ircUser)!==false || strpos($result, "PRIVMSG ".$this->ircChannel.' :!')!==false){
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
			    $this->increasePacketCount();
				foreach($this->_responder as $responder){
					$responder->call($packet, $insim, $this);
				}
			} else {
			}
            usleep(100000);
		}
		$insim->disconnect();
	}

	/**
	 * Send an IRC message to $receiver, where $receiver is either an user or channel name.
	 *
	 * @param string $input
	 * @param string $receiver
	 */
	public function sendMessage($input, $receiver = ''){
        if($receiver == ''){
            $receiver = $this->ircChannel;
        }
		$this->sendCommand('PRIVMSG '.$receiver.' :'.$input);
	}

    /**
     * Send an IRC notice to $receiver, where $receiver is either an user or channel name.
     *
     * @param string $input
     * @param string $receiver
     */
    public function sendNotice($input, $receiver = ''){
        if($receiver == ''){
            $receiver = $this->ircChannel;
        }
        $this->sendCommand('NOTICE '.$receiver.' :'.$input);
    }

	/**
	 * Sends a command to the connected IRC server. If $noRead is set to false, this function will try to read
	 * the server's answer and will return this as $return
	 *
	 * @param string $input
	 * @param bool $noRead
	 * @return string
	 */
	public function sendCommand($input, $noRead = true){
		$this->log($input);
		fwrite($this->_conn, $input."\n\r");
		if(!$noRead) {
			$return = fread($this->_conn, 1024);
			$this->log($return);
		}
		return $return;
	}

	/**
	 * Sends an TOPIC command to the IRC server and changes the topic in the defined channel
	 *
	 * @param int $numConns
	 */
	public function setIrcTopic($numConns = 0){
        $this->sendCommand("TOPIC ".$this->ircChannel." :".sprintf(TOPIC_TEMPLATE, $numConns).$this->getTopic());
	}

    /**
     * Write some output to the console
     *
     * @param string $text
     */
    public function log($text){
        echo @date("[Y-m-d H:i:s] ").$text.PHP_EOL;
    }
}
