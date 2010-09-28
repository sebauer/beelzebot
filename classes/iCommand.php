<?php

interface iCommand {
    public function isResponsible($command);
    public function call($inputLine, InSim $insim, Bot $bot);
    public function handleCall($command, $text, $sender, InSim $insim, Bot $bot);
    public function getHelp(Bot $bot, $sender);
}