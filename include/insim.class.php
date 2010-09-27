<?php

/******************************************************************************************\
|*  Class InSim (insim.class.php)      Version 0.8.6              Last modified: 2007/05/20
|*  =======================================================================================
|*  This file handles the complete connection to a LFS server using the InSim specification.
|*
|*  Some parts of the code have been taken from the LFS Wiki
|*  (http://en.lfsmanual.net/wiki/InSim_PHP5-Tutorial) but have been rewritten in order to
|*  work also with PHP >4.2.x versions.
|*  This class has no constructor to avoid "init" spamming of the server. The first step
|*  for initialization is to run the isi() (InSimInit) function.
|*
|*  This file still neeeds a lot of commentary and documentation, I know ;) Will do this
|*  ASAP.
|*
|*  LFS InSim Filetypes for PHP:
|*  char -> a
|*  byte -> C
|*  word -> S
|*  short -> s
|*  unsigned -> L
|*  int -> l
|*  float -> f
|*
|*  Written by Sebastian Bauer
\******************************************************************************************/

define('VER_INSIM', 4);
define('VER_LFS', '0.5W26');

require_once('insim.defines.php');

/**
 * InSim
 *
 * @package
 * @author sbauer
 * @copyright Copyright (c) 2007
 * @version $Id$
 * @access public
 */
class InSim {

  var $insimIP;
  var $insimPort;
  var $adminPW;
  var $localport;
  var $client;
  var $receiver;
  var $debug;
  var $lfsVersion;
  var $lfsProduct;
  var $inSimVersion;
  var $hostname;
  var $numRacers;
  var $numConnections;
  var $raceInProgress;
  var $qualMins;
  var $raceLaps;
  var $raceCurrency;
  var $track;
  var $weather;
  var $wind;


  /**
   * InSim::isi()
   *
   * This is the initialization method of the class, the constructor.
   * It establishes a connection to the server with the given parameters.
   *
   * If the connection fails, the script will die with an error message.
   *
   * @param string $ip
   * @param integer $port
   * @param string $pass
   * @return none
   */
  function isi($ip = '127.0.0.1', $port = 29999, $pass = '') {
    // ISI packet (InSimInit) to initialize a connection

    // CONFIG START
    $this->insimIP = $ip; // Your InSim-IP Here
    $this->insimPort = $port;     // Your InSim-Port
    $this->adminPW = $pass;  // Your Admin-Password
    // CONFIG END
    if($this->debug) {
      $i = strlen($this->adminPW);
      $j = 0;
      while($j < $i) {
        $showPass .= '*';
        $j++;
      }
      echo 'connecting to ' . $this->insimIP . ':' . $this->insimPort . ' with password ' . $showPass . '<br />';
    }

    // create sender filestream
    $errno = 0; $errstr = "";
    $this->client = @fsockopen('udp://' . $this->insimIP, $this->insimPort, &$errno, &$errstr, 3);
    if (!$this->client) {
      die("Error:\nCould not connect to $this->insimIP:$this->insimPort\nError Number: $errno\nError Description: $errstr");
    }
    if($this->debug) echo "connected!<br />\n";
    // create receiver filestream
    $this->localport = 30000;
    $this->receiver = false;
    while ($this->localport <= 65535) {
      $this->receiver = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
      $res = @socket_bind($this->receiver, '0.0.0.0', $this->localport);
      if ($res) {
        break;
      }
      $this->localport++;
      $this->receiver = false;
    }
    if ($this->receiver === false) {
      die("Error:\nCould not bind to $this->localport\nError Number: $errno\nError Description: $errstr");
    }
    if($this->debug)
      echo "setting local listening port to " . $this->localport . "<br />\n";

    // Make the receiver stream nonblocking to be able to apply timeouts
    socket_set_block($this->receiver);

    /*
      VARIABLES AFTER HERE:
      $client    : sender   filestream
      $receiver  : receiver filestream
      $localport : port of receiver filestream
      + config variables
    */

    // We will now have to send an ISI (InSimInit)-packet to InSim to make it accept our requests.
    // Prepare packet
    $packet  = pack("C", intval(44));         // Size of packet
    $packet .= pack("C", intval(ISP_ISI));    // ISP_ISI
    $packet .= pack("C", intval(1));          // byte   ReqI; If non-zero LFS will send an IS_VER packet
    $packet .= pack("C", intval(0));          // byte   Zero; 0


    $packet .= pack("S", $this->localport);   // response port
    $packet .= pack("S", 2+4+32);             // Connection Flags - see InSim.txt

    $packet .= pack("c", intval(0));          // Sp0
    $packet .= pack("c", intval(0));          // Prefix
    $packet .= pack("S", 1);                  // NodeSecs - time between packages

     if (strlen($this->adminPW) > 16) {
      $this->adminPW = substr($this->adminPW, 0, 16);      // Cut down adminpw if too long
    }
    $packet .= str_pad($this->adminPW, 16, "\0");    // Admin-Password if set in LFS host options
    $packet .= str_pad("LFSWebControl", 16, "\0");    // Admin-Password if set in LFS host options

    // Send packet
    fwrite($this->client, $packet, strlen($packet)); // Third parameter to make PHP ignore magic_quotes-setting

    // get server version
    $this->getVersion(false); // We only want to receive a version packet, we don't want to request another one
                              // as this already has been done by the fourth byte of the ISP_ISI packet not beeing 0
    // check server versions
    $this->checkVersion();
    // get server hostname
    $this->getHostname();
    // Now request StatePack
    $this->getStatePack();

  }

  /**
   * InSim::disconnect()
   *
   * This function will close the InSim connection. If you are closing the connection because
   * of a problem, you can use the parameter $error and an error message will be thrown.
   *
   * @access public
   * @param string $error   The error to be shown if you are disconnecting because of a problem
   * @return
   */
  function disconnect ($error = '') {

    if(!empty($error)) {
      echo "FATAL ERROR: " . $error . "<br />\n";
      unset($this->receiver);
      unset($this->client);
    }
    if($this->debug) echo "Requesting connection to be closed...<br />\n";
    $packet = $this->makeTiny(TINY_CLOSE);
    @fwrite($this->client, $packet, strlen($packet));
    @fclose($this->client);
    if($this->debug) echo "Connection closed...<br />\n";
    @socket_close($this->receiver);
    if($this->debug) echo "Listening socket closed...<br />\n";
  }

  /**
   * InSim::debug()
   *
   * Enables / disables debug mode (showing additional information).
   * If no parameter is given, the current status will be returned.
   *
   * @access public
   * @param bool $switch
   * @return
   */
  function debug ( $switch = '' ) {
    switch($switch) {
      case 1:
        $this->debug = true;
        break;
      case 0:
        $this->debug = false;
        break;
      default:
        return $this->debug;
        break;
    }
  }


  /**
   * InSim::makeTiny()
   *
   * This function will create a ISP_TINY packet of the type needed.
   *
   * @access private
   * @param mixed $subtype  The type of the ISP_TINY packet. See insim.defines.php
   * @return $packet A formatted insim packet
   */
  function makeTiny( $subtype ) {
    $packet  = pack("C", intval(4));         // Size of packet
    $packet .= pack("C", intval(3));          // ISP_TINY
    $packet .= pack("C", intval(1));          // byte   ReqI; If non-zero LFS will send an IS_VER packet
    $packet .= pack("C", intval($subtype));          // byte    SubT; 0

    return $packet;
  }


  /**
   * InSim::getHostname()
   *
   * This function requests a ISP_ISM packet containing the hostname
   * of the server.
   *
   * @access private
   * @return bool
   */
  function getHostname () {

    if(empty($this->receiver) && empty($this->client)) {
      return false;
    }

    // Now we request an InSimMulti-Package to get the LfS Hostname (if LfS is in multiplayer mode)
    // To perform the request, we simply send an InSimPack with ID = "ISM" and Value = 0.
    $packet = $this->makeTiny(TINY_ISM);
    if($this->debug) echo "Sending TINY_ISM packet to receive hostname...<br />\n";
    // send packet
    fwrite($this->client, $packet, strlen($packet));

    $packet = false;
    // receive answer from LfS: a packet with ID "ISM"

    $timeout = time() + 2;
    while (!$packet && time() <= $timeout) {
      $result = @socket_recv($this->receiver, $packet, 256, 0);
        if ($result != 0 && $result != FALSE)
        break;
    }
    // check if really a ISM-packet arrived or something else we cant deal with at the moment
    if (!$packet || $packet[1] != pack("C", ISP_ISM)) {
      echo "No hostname packet received.. (Packet is of type " . unpack("C", $packet[1])  . ")<br />\n";
      return false;
    }
    // Get LfS connection type: are we connected to a client (0) or a server (1)?
    $type_raw = substr($packet, 4, 1);
    $temp = unpack("c", $type_raw);
    $type = $temp[1];

    // Get LfS Hostname
    $this->hostname = trim(substr($packet, 8, 32));
    if(empty($this->hostname)) {
      echo "Not in multiplayer mode..<br />\n";
      return false;
    }
    else
      return true;
  }


  /**
   * InSim::getVersion()
   *
   * This function requests a version information packet if the parameter $dontSend
   * is set to true. The version information will be parsed afterwards.
   * If the fourth byte of IS_ISI has been set it is not required to request another
   * version packet, we just receive and parse one.
   *
   * @access private
   * @param bool $dontSend
   * @return bool   true on success, false on failure
   */
  function getVersion ($dontSend = false) {

    if(empty($this->receiver) && empty($this->client)) {
      return false;
    }
    // We only request the Version package if $dontSend is set. This will be done, as an init package
    // can automatically request the version and re-requesting the version would be senseless in this case.
    if($dontSend) {
      if($this->debug) echo "Sending TINY_VER packet to receive version...<br />\n";

      $packet = $this->makeTiny(TINY_VER);
      // send packet
      fwrite($this->client, $packet, strlen($packet));
    }

    $packet = false;

    // receive answer from LfS: a packet with ID "VER"

    $timeout = time() + 2;
    while (!$packet && time() <= $timeout) {
        $result = @socket_recv($this->receiver, $packet, 256, 0);
        if ($result != 0 && $result != FALSE) {
          break;
        }
    }

    // check if really a version-packet arrived or something else we cant deal with at the moment
    if (!$packet || $packet[1] != pack("C", ISP_VER)) {
      echo "No version packet received..<br />\n";
      return false;
    }
    else {
        // Parse version-package
        $this->lfsVersion = trim(substr($packet,  4, 8)); // char
        $this->lfsProduct = trim(substr($packet, 12, 6)); // char
        $isv_raw = substr($packet, 18, 2);          // word
      if(strlen($isv_raw) < 2) {
        $isv_raw = str_pad($isv_raw, 2, "\0");
        }
      $temp = unpack("S",$isv_raw);
      $this->inSimVersion = $temp[1];

      if(substr($this->lfsVersion, 4,1)=='0')
        $this->lfsVersion = substr($this->lfsVersion, 0, 4);

      return true;
    }
  }


  /**
   * InSim::checkVersion()
   *
   * This version compares the currently available version information
   * against the version required. A version packet MUST be received and
   * proceeded by getVersion() before!
   *
   * @access private
   * @return no return
   */
  function checkVersion () {

    if($this->inSimVersion < VER_INSIM) {
      $this->disconnect('The InSim version used by this server is too old!');
    }
    if(substr($this->lfsVersion,0,1) < substr(VER_LFS,0,1)) {
      $this->disconnect('Incompatible LFS version!');
    }
    if((substr($this->lfsVersion,0,1) == substr(VER_LFS,0,1)) && (substr($this->lfsVersion,2,1) < substr(VER_LFS,2,1))) {
      $this->disconnect('Incompatible LFS version!');
    }
    if((substr($this->lfsVersion,0,1) == substr(VER_LFS,0,1)) && (substr($this->lfsVersion,2,1) == substr(VER_LFS,2,1)) && (ord(substr($this->lfsVersion,3,1)) < ord(substr(VER_LFS,3,1)))) {
      $this->disconnect('Incompatible LFS version!');
    }
  }

  /**
   * InSim::getStatePack()
   *
   * Requests a ISP_STA state packet from the LFS Server containing
   * information about current server settings, players online etc.
   *
   * @access private
   * @return bool   true on success, false on failure
   */
  function getStatePack () {

    if(empty($this->receiver) && empty($this->client)) {
      return false;
    }

    $packet = $this->makeTiny(TINY_SST);

    if($this->debug) echo "Sending TINY_SST packet to receive StatePack...<br />\n";
    // send packet
    fwrite($this->client, $packet, strlen($packet));

    $packet = false;

    if($this->debug) echo "trying to get StatePack from insim..<br />\n";

    $timeout = time() + 2;
    while (!$packet && time() <= $timeout) {
        $result = @socket_recv($this->receiver, $packet, 256, 0);
        if ($result != 0 && $result != FALSE) {
          break;
        }
    }

    // check if really a statePack arrived or something else we cant deal with at the moment
    if (!$packet || $packet[1] != pack("C", ISP_STA)) {
        var_dump(unpack("C", pack("C", ISP_STA)));
        var_dump(unpack("C", $packet[0]));
        var_dump(unpack("C", $packet[1]));
        var_dump(unpack("C", $packet[2]));
        var_dump(unpack("C", $packet[3]));
        var_dump(unpack("C", $packet[4]));
      if($this->debug) echo "No StatePack packet received (Packet is of type " . unpack("C", $packet[1])  . ")<br />\n";
      return false;
    }
    else {
        // Parse version-package
        // get number of players in race
        $byte_packed = unpack("c", substr($packet, 12, 1)); // byte
        $this->numRacers = $byte_packed[1];
        // get number of connections (including host)
        $byte_packed = unpack("c", substr($packet, 13, 1)); // byte
        $this->numConnections = $byte_packed[1];
        // get status of race (0 - no / 1 - race / 2 - qualifying)
        $byte_packed = unpack("c", substr($packet, 15, 1)); // byte
        $this->raceInProgress = $byte_packed[1];
        // get amount of qualifying minutes
        $byte_packed = unpack("c", substr($packet, 16, 1)); // byte
        $this->qualMins = $byte_packed[1];
        // get amount of laps
        $laps = ord(substr($packet, 17, 1)); // byte
        if($laps == 0)
          $this->raceLaps = 'Practice';
        else {
          if($laps < 100) {
            $this->raceLaps = $laps;
            $this->raceCurrency = 'Laps';
          }
          else if($laps > 99 && $laps <= 190) {
            $this->raceLaps = ($laps-100) * 10 + 100;
            $this->raceCurrency = 'Laps';
          }
          else {
            $this->raceLaps = $laps - 190;
            $this->raceCurrency = 'Hours';
          }
        }
        // get track name
        $this->track = trim(substr($packet, 20, 6)); // char
        // get weather info
        $byte_packed = unpack("c", substr($packet, 26, 1)); // byte
        $this->weather = $byte_packed[1];
        // get wind info
        $byte_packed = unpack("c", substr($packet, 27, 1)); // byte
        $this->wind = $byte_packed[1];
      return true;
    }
  }

  /**
   * InSim::reInit()
   *
   * This function re-initializes the server by sending a text message
   * with a reinit command.
   * A message and a countdown will be shown to all connected clients.
   * If no one is connected, the restart will be done immediately.
   *
   * @access public
   * @param string $message
   * @return bool   false on failure
   */
  function reInit ( $message = '^1Server will be restarted..') {

    if(empty($this->receiver) && empty($this->client)) {
      return false;
    }

    // We only show all this blahblah stuff only if there's somebody on the server, otherwise simply reinit.
    if($this->numConnections > 1) {
        $this->sendCommand('rcm', $message);
        $this->sendCommand('rcm_all');
      sleep(5);
      $this->sendCommand('rcc_all');

        for($i = 5; $i > 0; $i--) {
          $this->sendCommand('msg', '^1Server will be restarted in ' . $i . '..');
          sleep(1);
        }
        $this->sendCommand('msg', '^1The Server will now be restarted.');
        sleep(2);
    }
    $this->sendCommand('reinit');
  }

  /**
   * InSim::sendTextMessage()
   *
   * This function will send a default text message to the server.
   *
   * If the message is longer than the allowed size it will be wrapped into
   * multiple messages.
   *
   * Since 0.5W26 there is a new packet type ISP_MSX (message extended) which
   * allows up to 96 byte long messages instead of 64 byte. As ISP_MSX is not
   * capable for commands we have the parameter $isCommand which will be set
   * to true when using sendTextMessage() in sendCommand().
   *
   * @access public
   * @param mixed $text
   * @param bool $isCommand
   * @return bool   false on failure
   */
  function sendTextMessage ( $text, $isCommand = false ) {

    if(empty($this->receiver) && empty($this->client)) {
      return false;
    }

    if( $isCommand ) {
        $maxLen = 64;
        $packetType = ISP_MST;
    } else {
        $maxLen = 96;
        $packetType = ISP_MSX;
    }

    if(strlen($text) > $maxLen) {
        while($i < strlen($text)) {
            $string = substr($text, $i, $maxLen-1);

            $packet = pack("c", $maxLen+4);
            $packet .= pack("c", $packetType);
            $packet .= pack("c", 1);
            $packet .= pack("c", 0);
            $packet .= str_pad($string, $maxLen, "\0");

            if($this->debug) echo "sending partial message '" . $string . "'<br />\n";
            fwrite($this->client, $packet, strlen($packet)); // Third parameter to make PHP ignore magic_quotes-setting
            $i += strlen($string);
        }
      } else {
        // Will send a text message to the server
            $packet = pack("c", $maxLen+4);
            $packet .= pack("c", $packetType);
            $packet .= pack("c", 1);
            $packet .= pack("c", 0);
        $packet .= str_pad($text, $maxLen, "\0");
      // Send packet
        if($this->debug) echo "sending message '" . $text . "'<br />\n";
      fwrite($this->client, $packet, strlen($packet)); // Third parameter to make PHP ignore magic_quotes-setting";
      }
  }

  /**
   * InSim::sendCommand()
   *
   * This function is a wrapper of the sendTextMessage() function as this
   * just takes a parameter of the desired command and a parameter containing
   * parameters for the command.
   * The third parameter $concat will be the seperator between the command
   * and the parameter.
   *
   * @access public
   * @param string $command
   * @param string $param
   * @param string $concat
   * @return bool false on failure
   */
  function sendCommand ( $command, $param = '', $concat = ' ' ) {

    if(empty($this->receiver) && empty($this->client)) {
      return false;
    }

    // we have $concat as extra parameter as some commands could maybe use = instead of a space

    //sleep(1); // not sure if we need this. i could imagine, that there are some circumstances where the server needs
                // a small pause between the requests send to it.
    if($concat != ' ' &&  $concat != '=')
      $concat = ' ';  // Just to make sure, that no crap will be sent ;)
    $command = substr(trim('/' . $command .  $concat . $param), 0, 64);
    $this->sendTextMessage($command, true);
  }
}

?>
