<?php

/**
 *
 * @author sbauer
 *
 */
class CommandRebuildCombo extends aCommand {
    public function isResponsible($command){
        // Not responsible for this type of command
        if($command != 'REBUILDCOMBO') return false;
        return true;
    }

    public function handleCall($command, $text, $sender, InSim $insim, Bot $bot){
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

        $bot->log('Rebuilding combo at random combo generator..');
        $serverResult = file_get_contents('http://www.gjl-network.net/randomlfs/random.php?date='.$date.'&password='.$password.'&reset');
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
        $bot->sendMessage('Resets the combo for the given date and creates a new one', $sender);
        $bot->sendMessage('REBUILDCOMBO - Usage: /msg '.USERNAME.' REBUILDCOMBO <password> <date>', $sender);
    }
}