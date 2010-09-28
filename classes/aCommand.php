<?php

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
        $splitByColon = explode(':', $inputLine);
        $text = preg_replace("/^\!?[a-zA-Z]+\s?/", '', $splitByColon[2]);
        return $text;
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