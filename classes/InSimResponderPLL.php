<?php
class InSimResponderPLL extends aInSimResponder {

    public function isResponsible($packet){
        if($packet[1] != pack("C", ISP_PLL)) return false;
        return true;
    }

    public function handleCall($packet, InSim $insim, Bot $bot){
        $bot->log("Received ISP_PLL...");

        if($insim->numConnections==2){
            $this->log("Last player left, server now idle..");
            $bot->setServerIdle(true);
        }
    }
}