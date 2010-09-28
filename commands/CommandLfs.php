<?php

/**
 * CommandLfs
 *
 * Implements command for communicating from IRC to LFS
 *
 * Usage: /msg <botname> lfs <text> or !lfs <text>
 *
 * @author sbauer
 *
 */
class CommandLfs extends aCommand {
    public function isResponsible($command){
        // Not responsible for this type of command
        if($command != 'LFS' && $command != '!LFS') return false;
        return true;
    }

    public function handleCall($command, $text, $sender, InSim $insim, Bot $bot){
        if($text==''){
            $bot->sendMessage('No text given!', $sender);
            return false;
        }

        // Only echo message to IRC chan, when using long format
        // since !lfs will be posted to the channel anyways
        if($command!='!LFS') {
            $bot->sendMessage('[toLFS]'.$sender.': '.$text, CHANNEL);
        }
        // Send message to LFS
        $insim->sendTextMessage('[IRC]'.$sender.': '.$text);
        return true;
    }

    public function getHelp(Bot $bot, $sender){
        $bot->sendMessage('Sends a text message to LFS', $sender);
        $bot->sendMessage('LFS - Usage: /msg '.USERNAME.' LFS <text>', $sender);
        $bot->sendMessage('Alias: !lfs <text>', $sender);
    }
}