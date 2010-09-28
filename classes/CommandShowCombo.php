<?php

/**
 *
 * @author sbauer
 *
 */
class CommandShowCombo extends aCommand {

    public function isResponsible($command){
        // Not responsible for this type of command
        if($command != 'SHOWCOMBO') return false;
        return true;
    }

    public function handleCall($command, $text, $sender, InSim $insim, Bot $bot){

        $date = reset(explode(' ', $text));
        if($date == ''){
            $bot->sendMessage('No date param given!', $sender);
            break;
        }

        $bot->log('Retreiving combo from random combo generator..');
        $serverResult = file_get_contents(LFS_RANDOM_URL.'?date='.$date);
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
        $bot->sendMessage('Shows the combo for the given date', $sender);
        $bot->sendMessage('SHOWCOMBO - Usage: /msg '.USERNAME.' SHOWCOMBO <date>', $sender);
    }
}