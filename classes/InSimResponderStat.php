<?php
class InSimResponderStat extends aInSimResponder {

    public function isResponsible($packet){
        if($packet[1] != pack("C", ISP_STA)) return false;
        return true;
    }

    public function handleCall($packet, InSim $insim, Bot $bot){
        $bot->log("Received state pack..");
        $insim->handleStatePackage($packet);

        if($insim->numConnections==1){
            $bot->setServerIdle(true);
        }
        $connCount = intval($insim->numConnections - 1);
        if($connCount < 0) $connCount = 0;
        $bot->sendCommand("TOPIC ".CHANNEL." :".sprintf(TOPIC_TEMPLATE, $connCount));
    }
}