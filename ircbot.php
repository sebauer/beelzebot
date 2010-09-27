<?php

include_once('config.php');

echo 'Connecting to tiscali.dk.quakenet.org'.PHP_EOL;
$conn = fsockopen('tiscali.dk.quakenet.org', 6667);
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
    }
    if(strpos($result, "MODE ".USERNAME." +i")!==false){
        if (!$firstrun) {
            sendCommand("JOIN #octrin\n\r", $conn);
            $firstrun = true;
        }
    }
    if(strpos($result, "JOIN #octrin")!==false && !$connected){
        sendCommand("AUTH ".USERNAME." ".PASSWORD, $conn);
        sendCommand("PRIVMSG #octrin :lolol i'm in", $conn);
        $connected = true;
    }
    if($connected && (time()-$time)>60*5) {
        $time = time();

        $hosts = unserialize(gzdecode(file_get_contents("http://www.lfsworld.net/pubstat/get_stat2.php?version=1.4&idk=2FDVRzY1n7Xp3Vp93jSbHus4rtFMWvF2&action=hosts&s=2&c=1")));

        foreach($hosts as $host){
            if(strpos(strtolower($host['hostname']), 'octrin')===false){
                continue;
            }
            $nrofracers = intval($host['nrofracers']);
            echo "Racers online: ".$nrofracers.PHP_EOL;
            sendCommand("TOPIC #octrin :Octrin Racing - Racers online on server: $nrofracers", $conn);
        }
    }
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
