<?php
/**
 * Copyright (c) 2010 Sebastian Bauer
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Sebastian Bauer <sbauer@gjl-network.net>
 * @license MIT
 */

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