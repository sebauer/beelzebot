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

class CommandBeMyFriend extends aCommand {

	private $friendRequestCount = array( );

    public function isResponsible($command){
        // Not responsible for this type of command
        if($command != 'BEMYFRIEND' && $command != '!BEMYFRIEND') return false;
        return true;
    }

    public function handleCall($command, $text, $sender, InSim $insim, Bot $bot){

        $bot->sendNotice('Beelzebot does not need any friends!', $sender);
        if(!array_key_exists($sender, $this->friendRequestCount)){
        	$this->friendRequestCount[$sender] = 1;
        } else {
        	$this->friendRequestCount[$sender]++;
        }
        if($this->friendRequestCount[$sender] >= 2){
        	$bot->sendNotice('Don\'t make Beelzebot angry!', $sender);
        }
        if($this->friendRequestCount[$sender] >= 3){
        	$bot->sendNotice('Your fault! Our friendship cannot be!', $sender);
        	$bot->sendCommand('KICK '.CHANNEL.' '.$sender.' :Your fault, our friendship cannot be!');
        }
        return true;
    }

    public function getHelp(Bot $bot, $sender){
        $bot->sendNotice('Make friendship with beelzebot', $sender);
//        $bot->sendMessage('QUIT - Usage: /msg '.USERNAME.' QUIT <auth_password>', $sender);
    }
}