<?php

interface iInSimResponder {
    public function call($packet, InSim $insim, Bot $bot);
    public function handleCall($packet, InSim $insim, Bot $bot);
    public function isResponsible($packet);
}