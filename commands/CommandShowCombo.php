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
            $bot->sendNotice('No date param given!', $sender);
            return false;
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
        $bot->sendNotice('Shows the combo for the given date', $sender);
        $bot->sendNotice('SHOWCOMBO - Usage: /msg '.USERNAME.' SHOWCOMBO <date>', $sender);
    }
}