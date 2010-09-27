<?php

define('CHANNEL', '#octrin');
define('SERVER', 'clanserver4u.de.quakenet.org');

include_once('config.php');

echo 'Connecting to '.SERVER.PHP_EOL;
$conn = fsockopen(SERVER, 6667);
echo 'Connected'.PHP_EOL;

sendCommand("USER ".USERNAME." 0 0 ".USERNAME, $conn, false);
sendCommand("NICK ".USERNAME, $conn);
$connected = false;
$time = time();
while(!feof($conn)){
    $result = fread($conn, 1024);
    $part = explode(" ",$result);
    echo $result.PHP_EOL;

    if($part[0] == "PING")
    {
        $ping = explode(":", $result);
        $reply = $ping[1];
        sendCommand("PONG $reply\n\r", $conn);
        if (!$firstrun) {
            sendCommand("JOIN ".CHANNEL."\n\r", $conn);
            sendCommand("AUTH ".USERNAME." ".PASSWORD, $conn);
            $firstrun = true;
        }
    }
    if($connected && (time()-$time)>60*5) {
        $time = time();

        $hosts = unserialize(gzuncompress(file_get_contents("http://www.lfsworld.net/pubstat/get_stat2.php?version=1.4&idk=2FDVRzY1n7Xp3Vp93jSbHus4rtFMWvF2&action=hosts&s=2&c=2")));

        foreach($hosts as $host){
            if(strpos(strtolower($host['hostname']), 'octrin')===false){
                continue;
            }
            $nrofracers = intval($host['nrofracers']);
            echo "Racers online: ".$nrofracers.PHP_EOL;
            sendCommand("TOPIC ".CHANNEL." :Octrin Racing - Racers online on server: $nrofracers", $conn);
        }
    }

    // Work with incoming commands
    if(strpos($result, "PRIVMSG ".USERNAME)!==false){

        $result = str_replace(array("\n","\r"), '', $result);

        $split = explode(':', $result);
        $command = $split[2];
        $op = explode('!', $split[1]);
        $op = $op[0];
        $command = explode(' ', $command);
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
            case 'help':
                sendMessage('Available commands: HELP, CREATECOMBO, REBUILDCOMBO, SHOWCOMBO', $op, $conn);
                sendMessage('Syntax for combo commands: /msg '.USERNAME.' <command> [[<password>] <date>]', $op, $conn);
                time_nanosleep(0, 500000000);
                sendMessage('Sample: /msg '.USERNAME.' CREATECOMBO secretpassword TestEvent', $op, $conn);
                sendMessage('Sample: /msg '.USERNAME.' SHOWCOMB TestEvent', $op, $conn);
                break;
            default:
                sendMessage('Unknown command "'.$command.'"!', $op, $conn);
        }
    }
}

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
