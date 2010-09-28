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
        $bot->sendMessage('Available Commands:', $sender);
        $commands = $bot->getCommands();
        $commandList = array( );
        foreach($commands as $commandString => $command) {
            $commandList[] = str_replace('COMMAND', '', $commandString);
        }
        $bot->sendMessage(implode(', ', $commandList), $sender);
        $bot->sendMessage('To get help on a specific command, use HELP:', $sender);
        $bot->sendMessage('HELP - Usage: /msg '.USERNAME.' HELP <command>', $sender);
    }
}