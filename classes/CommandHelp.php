<?php

class CommandHelp extends aCommand {
    public function isResponsible($command){
        // Not responsible for this type of command
        if($command != 'HELP') return false;
        return true;
    }

    public function handleCall($command, $text, $sender, InSim $insim, Bot $bot){
        $helpCmd = strtoupper(reset(explode(' ', $text)));

        $bot->log('Looking for help about '.$helpCmd);

        if($helpCmd == ''){
            $this->getHelp($bot, $sender);
            return true;
        }
        $commandClass = $bot->getCommand($helpCmd);
        if(!$commandClass){
            $bot->sendMessage('Unknown command "'.$helpCmd.'"', $sender);
            return false;
        }
        $commandClass->getHelp($bot, $sender);
        return true;
    }

    public function getHelp(Bot $bot, $sender){
        $bot->sendMessage('Shows help for a command', $sender);
        $bot->sendMessage('HELP - Usage: /msg '.USERNAME.' HELP <command>', $sender);
    }
}