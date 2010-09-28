<?php
require_once('include/bootstrap.php');

$bot = new Bot();


$bot->log('Loading Commands...');

$commandFiles = scandir('classes');

foreach($commandFiles as $commandFile){
    if(preg_match('/^Command[a-zA-Z]+\.php$/', $commandFile)==0)continue;

    $className = str_replace('.php', '', $commandFile);
    $bot->log('Loading command '.$className.'..');
    $command = new $className();
    $bot->addCommand($command);
}


$bot->log('Loading InSimResponders...');

$commandFiles = scandir('classes');

foreach($commandFiles as $commandFile){
    if(preg_match('/^InSimResponder[a-zA-Z]+\.php$/', $commandFile)==0)continue;

    $className = str_replace('.php', '', $commandFile);
    $bot->log('Loading InSimResponder '.$className.'..');
    $command = new $className();
    $bot->addInSimResponder($command);
}

$bot->run();

?>
