<?php

class CommandQuit extends aCommand {
    public function isResponsible($command){
        // Not responsible for this type of command
        if($command != 'QUIT') return false;
        return true;
    }

    public function handleCall($command, $text, $sender, InSim $insim, Bot $bot){
        if($text != PASSWORD){
            $bot->sendMessage('Wrong password given!', $sender);
            return false;
        }
        $insim->sendTextMessage('IRC Bot is now leaving.. Bye, bye!');
        $bot->sendCommand('QUIT');
        return true;
    }

    public function getHelp(Bot $bot, $sender){
        $bot->sendMessage('Disconnects the bot and closes all connections:', $sender);
        $bot->sendMessage('QUIT - Usage: /msg '.USERNAME.' QUIT <auth_password>', $sender);
    }
}