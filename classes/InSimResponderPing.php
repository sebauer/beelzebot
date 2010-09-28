<?php
class InSimResponderPing extends aInSimResponder {

    public function isResponsible($packet){
        if($packet[1] != pack("C", ISP_TINY)) return false;
        return true;
    }

    public function handleCall($packet, InSim $insim, Bot $bot){

        if($bot->getPacketCount() <= 1){
            $bot->log("No activity since last InSim PING, going idle...");
            $bot->setServerIdle(true);
        }
        $bot->resetPacketCount();
        $bot->log("Received IS_TINY, replying..");
        $insim->sendTiny($insim->makeTiny(TINY_NONE));
    }
}