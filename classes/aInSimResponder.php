<?php

abstract class aInSimResponder implements iInSimResponder{

    final public function call($packet, InSim $insim, Bot $bot){
        if(!$this->isResponsible($packet)) return;

        return $this->handleCall($packet, $insim, $bot);
    }
}