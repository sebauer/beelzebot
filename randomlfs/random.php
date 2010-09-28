<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<pre>
Octrin.Racing Random Car/Track Combo Generator
==================================================

<?php
define('SPERRFRIST', 5);
define('PASSWORD', 'dasdarfnurich');
$timestamp = date('Y-m-d H:i:s');
$reset = array_key_exists('reset', $_GET);
$password = $_GET['password'];

$tracks = array( );
///////////////////////////
// BLACKWOOD
///////////////////////////
$tracks["BL1"] = "Blackwood GP";
$tracks["BL1R"] = "Blackwood GP Rev.";
$tracks["BL2"] = "Blackwood RallyX";
$tracks["BL2R"] = "Blackwood RallyX Rev.";
$tracks["BL3"] = "Blackwood Car Park";

///////////////////////////
// SOUTH CITY
///////////////////////////
$tracks["SO1"] = "South City Classic";
$tracks["SO1R"] = "South City Classic Rev.";
$tracks["SO2"] = "South City Sprint Track 1";
$tracks["SO2R"] = "South City Sprint Track 1 Rev.";
$tracks["SO3"] = "South City Sprint Track 2";
$tracks["SO3R"] = "South City Sprint Track 2 Rev.";
$tracks["SO4"] = "South City Long";
$tracks["SO4R"] = "South City Long Rev.";
$tracks["SO5"] = "South City Town Course";
$tracks["SO5R"] = "South City Town Course Rev.";

///////////////////////////
// FERN BAY
///////////////////////////
$tracks["FE1"] = "Fern Bay Club";
$tracks["FE1R"] = "Fern Bay Club Rev.";
$tracks["FE2"] = "Fern Bay Green";
$tracks["FE2R"] = "Fern Bay Green Rev.";
$tracks["FE3"] = "Fern Bay Gold";
$tracks["FE3R"] = "Fern Bay Gold Rev.";
$tracks["FE4"] = "Fern Bay Black";
$tracks["FE4R"] = "Fern Bay Black Rev.";
$tracks["FE5"] = "Fern Bay Rallycross";
$tracks["FE5R"] = "Fern Bay Rallycross Rev.";
$tracks["FE6"] = "Fern Bay RallyX Green";
$tracks["FE6R"] = "Fern Bay RallyX Green Rev.";

///////////////////////////
// AUTOCROSS
///////////////////////////
$tracks["AU1"] = "Autocross";
$tracks["AU2"] = "Autocross Skid Pad";
$tracks["AU3"] = "Autocross Drag Strip";
$tracks["AU4"] = "Autocross 8 Lane Drag Strip";

///////////////////////////
// KYOTO
///////////////////////////
$tracks["KY1"] = "Kyoto Ring Oval";
$tracks["KY1R"] = "Kyoto Ring Oval Rev.";
$tracks["KY2"] = "Kyoto Ring National";
$tracks["KY2R"] = "Kyoto Ring National Rev.";
$tracks["KY3"] = "Kyoto Ring GP Long";

$tracks["KY3R"] = "Kyoto Ring GP Long Rev.";

///////////////////////////
// WESTHILL
///////////////////////////
$tracks["WE1"] = "Westhill International";
$tracks["WE1R"] = "Westhill International Rev.";

///////////////////////////
// ASTON
///////////////////////////
$tracks["AS1"] = "Aston Cadet";
$tracks["AS1R"] = "Aston Cadet Rev.";
$tracks["AS2"] = "Aston Club";
$tracks["AS2R"] = "Aston Club Rev.";
$tracks["AS3"] = "Aston National";
$tracks["AS3R"] = "Aston National Rev.";
$tracks["AS4"] = "Aston Historic";
$tracks["AS4R"] = "Aston Historic Rev.";
$tracks["AS5"] = "Aston Grand Prix";
$tracks["AS5R"] = "Aston Grand Prix Rev.";
$tracks["AS6"] = "Aston Grand Touring";
$tracks["AS6R"] = "Aston Grand Touring Rev.";
$tracks["AS7"] = "Aston North";
$tracks["AS7R"] = "Aston North Rev.";

$cars = array('UF1','XFG','XRG','XFG+XRG','LX4','LX6','RB4','FXO','XRT','TBO','RAC','FZ5','RAC+FZ5','UFR','XFR','NGT','FXR','XRR','FZR','GTR','MRT','FBM','FOX','F08','BF1'
);

$excludeTracks = array('AU1', 'AU2', 'AU3', 'AU4', 'BL3');
$excludeCombos = array('BL2UFR','BL2XFR','BL2NGT','BL2FXR','BL2XRR','BL2FZR','BL2GTR','BL2MRT','BL2FBM','BL2FOX','BL2F08','BL2BF1','BL2RUFR','BL2RXFR','BL2RNGT','BL2RFXR','BL2RXRR','BL2RFZR','BL2RGTR','BL2RMRT','BL2RFBM','BL2RFOX','BL2RF08','BL2RBF1','FE5UFR','FE5XFR','FE5NGT','FE5FXR','FE5XRR','FE5FZR','FE5GTR','FE5MRT','FE5FBM','FE5FOX','FE5F08','FE5BF1','FE5RUFR','FE5RXFR','FE5RNGT','FE5RFXR','FE5RXRR','FE5RFZR','FE5RGTR','FE5RMRT','FE5RFBM','FE5RFOX','FE5RF08','FE5RBF1','FE6UFR','FE6XFR','FE6NGT','FE6FXR','FE6XRR','FE6FZR','FE6GTR','FE6MRT','FE6FBM','FE6FOX','FE6F08','FE6BF1','FE6RUFR','FE6RXFR','FE6RNGT','FE6RFXR','FE6RXRR','FE6RFZR','FE6RGTR','FE6RMRT','FE6RFBM','FE6RFOX','FE6RF08','FE6RBF1','KY1UF1','KY1XFG','KY1XRG','KY1RUF1','KY1RXFG','KY1RXRG');

echo 'Exkludierte Strecken: '.implode(', ', $excludeTracks).PHP_EOL;
echo 'Exkludierte Kombos: '.implode(', ', $excludeCombos).PHP_EOL;
echo 'Sperrfrist für Autos/Strecken: '.SPERRFRIST.' Termine'.PHP_EOL;
echo '-----------------------------------------------------------------------------------------------'.PHP_EOL;

if(!array_key_exists('date', $_GET) || $_GET['date'] == ''){
	die('Kein Datum angegeben ( z.B. date=2010-10-20 )');
}
$date = $_GET['date'];

$usedTracks = unserialize(file_get_contents('used_tracks'));
if($usedTracks == false){
	$usedTracks = array( );
}
$usedCars = unserialize(file_get_contents('used_cars'));
if($usedCars == false){
	$usedCars = array( );
}
$usedCombos = unserialize(file_get_contents('used_combos'));
if($usedCombos == false){
	$usedCombos = array( );
}
echo 'Benutzte Kombos:'.PHP_EOL;
foreach($usedCombos as $combDate => $combo){
	echo "$combDate\t$combo".PHP_EOL;
}

echo '-----------------------------------------------------------------------------------------------'.PHP_EOL;

$log = unserialize(file_get_contents('log'));
if($log == false){
	$log = array( );
}

if(array_key_exists($date, $usedCombos) && !$reset) {
	echo 'Kombo für '.$date.': '.$usedCombos[$date].PHP_EOL.PHP_EOL;
	echo "Um eine neue Kombo für dieses Datum zu generieren und die verwendete wieder aus der Blacklist zu streichen,\nzusätzlich den Parameter reset=1 übergeben!".PHP_EOL.PHP_EOL;
	echo 'Logfile für dieses Datum:'.PHP_EOL.PHP_EOL;
	echo implode(PHP_EOL, $log[$date]);
	die();
}

if($password != PASSWORD){
	$log[$date][] = $timestamp.' Änderungsversuch mit falschem Passwort!';
	file_put_contents('log', serialize($log));
	die('Falsches Passwort');
}

echo 'Erzeuge neue Kombo...........'.PHP_EOL.PHP_EOL;
if($reset && array_key_exists($date, $usedCombos)){
	$log[$date][] = $timestamp.' Kombo resetted';
	echo 'Neue Kombo erzeugt..'.PHP_EOL.PHP_EOL;
	$oldCombo = $usedCombos[$date];
	unset($usedCombos[$date]);
	unset($usedCars[array_search(reset(explode('|', $oldCombo)), $usedCars)]);
	unset($usedTracks[array_search(next(explode('|', $oldCombo)), $usedTracks)]);
}

if(count($usedCars) == count($cars)){
	print "Alle Autos wurden bereits gefahren.";
	die();
}
if(count($usedTracks)+count($excludeTracks) >= count($tracks)){
	print "Alle Autos wurden bereits gefahren.";
	die();
}
$randTrack = null;
$randCar = null;
$tryCounter = 0;

while(true && $tryCounter <= 50){
	$tryCounter++;
	if($tryCounter == 50){
		$log[$date][] = $timestamp.' Keine Kombinationen mehr möglich';
		die('Keine Kombinationen mehr möglich');
	}
	$randTrack = array_rand($tracks);
	$randCar = $cars[array_rand($cars)];
	if(array_search($randTrack.$randCar, $usedCombos)===FALSE && array_search($randTrack, $excludeTracks)===FALSE && array_search($randTrack, $usedTracks)===FALSE && array_search($randCar, $usedCars)===FALSE && array_search($randTrack.'|'.$randCar, $excludeCombos)===FALSE){
		break;
	}
}

$usedCombos[$date] = $randTrack.'|'.$randCar;

print 'Kombo für '.$date.':'.PHP_EOL;
print "Track: ".$tracks[$randTrack] . ' ('.$randTrack.')'.PHP_EOL;
print "Car(s): ".$randCar.PHP_EOL;

if(count($log[$date])==0){
	$log[$date][] = $timestamp.' Datum angelegt..';
}
$log[$date][] = $timestamp.' Neue Kombo: '.$randTrack.'|'.$randCar;

array_unshift($usedTracks, $randTrack);
array_splice($usedTracks, SPERRFRIST);

array_unshift($usedCars, $randCar);
array_splice($usedCars, SPERRFRIST);

file_put_contents('used_tracks', serialize($usedTracks));
file_put_contents('used_cars', serialize($usedCars));
file_put_contents('used_combos', serialize($usedCombos));
file_put_contents('log', serialize($log));

?>
</pre>
</body>
</html>
