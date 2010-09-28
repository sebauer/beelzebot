<?php

/**
 *
 * @author sbauer
 *
 */
class CommandCreateCombo extends aCommand {
    public function isResponsible($command){
        // Not responsible for this type of command
        if($command != 'CREATECOMBO') return false;
        return true;
    }

    public function handleCall($command, $text, $sender, InSim $insim, Bot $bot){

        // Not responsible for this type of command
        if($command != 'CREATECOMBO') return;

        $password = reset(explode(' ', $text));
        $date = next(explode(' ', $text));

        if($password == ''){
            $bot->sendMessage('No password given!', $sender);
            return false;
        }
        if($date == ''){
            $bot->sendMessage('No date param given!', $sender);
            break;
        }
        $bot->log('Retreiving new combo from random combo generator..');
        $serverResult = file_get_contents('http://www.gjl-network.net/randomlfs/random.php?date='.$date.'&password='.$password);
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
            $bot->sendMessage($resultLine, CHANNEL);
            $insim->sendTextMessage('[IRC]: '.$resultLine);
            if($count%4==0) {
                time_nanosleep(0, 500000000);
            }
        }
        return true;
    }

    public function getHelp(Bot $bot, $sender){
        $bot->sendMessage('Creates a new combo for the given date', $sender);
        $bot->sendMessage('CREATECOMBO - Usage: /msg '.USERNAME.' CREATECOMBO <password> <date>', $sender);
    }
}