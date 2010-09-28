<?php
class Bot {

    private $_commands = array( );
    private $_responder = array( );
    private $_conn = null;
    private $_packetCount = 0;
    private $_serverIdle = true;

    public function setServerIdle($bool){
        $bool = (bool)$bool;
        if($bool){
            $this->log("Server currently idle..");
        } else {
            $this->log("Server currently active..");
        }
        $this->_serverIdle = $bool;
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

    public function run(){
        $this->log('Connecting to InSim '.INSIM_SERVER.':'.INSIM_PORT);

        $insim = new InSim();
        $insim->debug(1);

        $insim->isi(INSIM_SERVER, INSIM_PORT, INSIM_PASS);
        $this->log('InSim connected!'.PHP_EOL);

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
                $resultLfs = @socket_recv($insim->receiver, $packet, 1024, MSG_WAITALL);
            } else {
                $resultLfs = @socket_recv($insim->receiver, $packet, 1024, MSG_NOWAIT);
            }
            if(time() > $activityTimeout){
                $activityTimeout = time()+30;
                $this->log("Checking server idle status via LFSWorld..");
                $statFile = file_get_contents("http://www.lfsworld.net/hoststatus/?h=".urlencode(LFSHOST));
                if(strpos($statFile, '0 / ')!==false){
                    $serverActive = false;
                    $this->log("Server currently idle...");
                } else {
                    $serverActive = true;
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
                $this->sendCommand("AUTH ".USERNAME." ".PASSWORD, $this->_conn);
                $this->sendCommand("JOIN ".CHANNEL."\n\r", $this->_conn);
                $this->sendCommand("TOPIC ".CHANNEL." :Octrin Racing - Users online on server: ".intval($insim->numConnections - 1), $this->_conn);
                $this->sendMessage('**** Octrin LFS/IRC Bot starting its work.. - Revion .'._REVISION.' ****', CHANNEL, $this->_conn);
                $this->sendMessage('**** Get ready to rumble! ****', CHANNEL, $this->_conn);
                $firstrun = true;
            }

            // Work with incoming InSim Packets
            if($packet){

                $this->log("Processing packet..");
                foreach($this->_responder as $responder){
                    $responder->call($packet, $insim, $this);
                }
            }

            // Work with incoming commands
            if(strpos($result, "PRIVMSG ".USERNAME)!==false || strpos($result, "PRIVMSG ".CHANNEL.' :!')!==false){

                $this->log("Processing command..");
                $returnVal = false;
                foreach($this->_commands as $command){
                    $returnVal = $command->call($result, $insim, $this);
                    if($returnVal) {
                        break;
                    }
                }
                if($returnVal != true){
                    $this->log('No handler for command found!');
                    $this->sendMessage('Unknown command "'.str_replace(array("\n","\r"), '', aCommand::extractCommand($result)).'"', aCommand::extractSender($result));
                }
            }
        }
        $insim->disconnect();
    }

    public function sendMessage($input, $receiver = CHANNEL){
        $this->sendCommand('PRIVMSG '.$receiver.' :'.$input);
    }

    public function sendCommand($input, $noRead = true){
        $this->log($input);
        fwrite($this->_conn, $input."\n\r");
        if(!$noRead) {
            $return = fread($this->_conn,1024);
            $this->log($return);
        }
        return $return;
    }

    public function log($text){
        echo date("[Y-m-d h:i:s] ").$text.PHP_EOL;
    }
}