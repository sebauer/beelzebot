<?php

class FileLocator {
	public static function getCommands(Bot $bot){
		$commandFiles = scandir('commands');
		$commands = array( );
		foreach($commandFiles as $commandFile){
		    if(preg_match('/^Command[a-zA-Z]+\.php$/', $commandFile)==0)continue;
		
		    $className = str_replace('.php', '', $commandFile);
    		$bot->log('Loading command '.$className.'..');
		    $command = new $className();
		    $commands[] = $command;
		}
		return $commands;
	}
	
	public static function getResponders(Bot $bot){
		$responderFiles = scandir('insimresponders');
		$responders = array( );
		foreach($responderFiles as $responderFile){
		    if(preg_match('/^InSimResponder[a-zA-Z]+\.php$/', $responderFile)==0)continue;
		
		    $className = str_replace('.php', '', $responderFile);
		    $responder = new $className();
		    $responders[] = $responder;
		}
		return $responders;
	}
}