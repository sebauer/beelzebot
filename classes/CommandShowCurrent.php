<?php

/**
 *
 * @author sbauer
 *
 */
class CommandShowCurrent extends aCommand {
    public function isResponsible($command){
        // Not responsible for this type of command
        if($command != 'SHOWCURRENT') return false;
        return true;
    }

    public function handleCall($command, $text, $sender, InSim $insim, Bot $bot){
        $insim->sendTiny($insim->makeTiny(TINY_SST));
        $output = 'Host: ' . preg_replace("/\^.{1}/", '', $insim->hostname) . ', LFS product: ' . $insim->lfsProduct . ', LFS version: ' . $insim->lfsVersion . ', InSim version: ' . $insim->inSimVersion;
        $bot->sendMessage($output, $sender);
        $output = 'Num. Racers: ' . $insim->numRacers . ', Num. Connections: ' . $insim->numConnections;
        $bot->sendMessage($output, $sender);
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
        $bot->sendMessage($output, $sender);
        $output = 'Track: ' . getTrackName($insim->track) . ', Weather: ' . $insim->weather . ', Wind: ' . $insim->wind;
        $bot->sendMessage($output, $sender);

        return true;
    }

    public function getHelp(Bot $bot, $sender){
        $bot->sendMessage('Shows the current server status', $sender);
        $bot->sendMessage('SHOWCURRENT - Usage: /msg '.USERNAME.' SHOWCURRENT', $sender);
    }
}