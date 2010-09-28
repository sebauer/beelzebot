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


abstract class aCommand implements iCommand {

    static public function extractSender($inputLine){
        $result = str_replace(array("\n","\r"), '', $inputLine);
        $split = explode(':', $result);
        $op = explode('!', $split[1]);;
        return $op[0];
    }

    static public function extractCommand($inputLine){
        $splitByColon = explode(':', $inputLine);
        $command = explode(' ', $splitByColon[2]);
        $command = $command[0];
        return $command;
    }

    static public function extractText($inputLine){
        $result = array( );
        $ret = preg_match("/^:.+ PRIVMSG (".USERNAME."|".CHANNEL.") :\!?[a-zA-Z]+\s(.+)$/", $inputLine, $result);
        if($ret == 0){
            return '';
        }
        return $result[2];
    }

    final public function call($inputLine, InSim $insim, Bot $bot){

        $sender = $this->extractSender($inputLine);

        $inputLine = str_replace(array("\n","\r"), '', $inputLine);

        // Split string and extract command
        $command = strtoupper($this->extractCommand($inputLine));
        $text = $this->extractText($inputLine);

        if(!$this->isResponsible($command)) return;

        return $this->handleCall($command, $text, $sender, $insim, $bot);
    }
}