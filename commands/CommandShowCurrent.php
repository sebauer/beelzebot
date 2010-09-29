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
class CommandShowCurrent extends aCommand {
    public function isResponsible($command){
        // Not responsible for this type of command
        if($command != 'SHOWCURRENT') return false;
        return true;
    }

    public function handleCall($command, $text, $sender, InSim $insim, Bot $bot){
        $insim->sendTiny($insim->makeTiny(TINY_SST));
        $output = 'Host: ' . preg_replace("/\^.{1}/", '', $insim->hostname) . ', LFS product: ' . $insim->lfsProduct . ', LFS version: ' . $insim->lfsVersion . ', InSim version: ' . $insim->inSimVersion;
        $bot->sendNotice($output, $sender);
        $output = 'Num. Racers: ' . $insim->numRacers . ', Num. Connections: ' . $insim->numConnections;
        $bot->sendNotice($output, $sender);
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
        $bot->sendNotice($output, $sender);
        $output = 'Track: ' . getTrackName($insim->track) . ', Weather: ' . $insim->weather . ', Wind: ' . $insim->wind;
        $bot->sendNotice($output, $sender);

        return true;
    }

    public function getHelp(Bot $bot, $sender){
        $bot->sendNotice('Shows the current server status', $sender);
        $bot->sendNotice('SHOWCURRENT - Usage: /msg '.USERNAME.' SHOWCURRENT', $sender);
    }
}