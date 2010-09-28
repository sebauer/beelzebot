<?php
class InSimResponderMSO extends aInSimResponder {

    public function isResponsible($packet){
        if($packet[1] != pack("C", ISP_MSO)) return false;
        return true;
    }

    public function handleCall($packet, InSim $insim, Bot $bot){
        $bot->log("Received ISP_MSO...");

        $type_raw = substr($packet, 8, 128);
        if($previous != $type_raw){
            $previous = $type_raw;
            $text = $type_raw;
            $text = preg_replace("/\^.{1}/", '', $text);
            if(strpos($text, '!irc')){
                $text = str_replace('!irc ', '', $text);
                $bot->sendMessage('[LFS]'.$text, CHANNEL);
            }
        }
    }
}