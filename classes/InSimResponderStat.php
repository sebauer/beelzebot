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
        $bot->sendCommand("TOPIC ".CHANNEL." :Octrin Racing - Users online on server: ".intval($insim->numConnections - 1));
    }
}