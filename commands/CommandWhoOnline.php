<?php

/**
 * Copyright (c) 2011 Sebastian Bauer
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
 *
 * @author sbauer
 *
 */
class CommandWhoOnline extends aCommand {
    public function isResponsible($command){
        // Not responsible for this type of command
        if($command != 'WHOONLINE') return false;
        return true;
    }

    public function handleCall($command, $text, $sender, InSim $insim, Bot $bot){
        $insim->sendTiny($insim->makeTiny(TINY_NPL));
        $output = 'Players currently online:';
        $bot->sendNotice($output, $sender);
        $connections = $insim->getConnections();
        $output = '';
        $count = 0;
        foreach($connections as $connection) {
        	$output .= preg_replace("/\^.{1}/", "", $connection['nickname']) . ' (' . $connection['username'] . ') ';
        	if($count == count($connections)-2){
        		$output .= ', ';
        	}
        	$count++;
        }
        $bot->sendNotice($output, $sender);
        return true;
    }

    public function getHelp(Bot $bot, $sender){
        $bot->sendNotice('Shows the players currently connected to the server', $sender);
        $bot->sendNotice('WHOONLINE - Usage: /msg '.USERNAME.' WHOONLINE', $sender);
    }
}