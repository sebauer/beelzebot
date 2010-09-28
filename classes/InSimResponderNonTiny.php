<?php
class InSimResponderNonTiny extends aInSimResponder {

    public function isResponsible($packet){
        if($packet[1] == pack("C", ISP_TINY)) return false;
        return true;
    }

    public function handleCall($packet, InSim $insim, Bot $bot){
        $bot->increasePacketCount();
    }
}