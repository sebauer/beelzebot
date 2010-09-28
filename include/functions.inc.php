<?php

/**
 * Copyright (c) 2010 Sebastian Bauer
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Sebastian Bauer <sbauer@gjl-network.net>
 * @license MIT
 */


/***************************************************\
|*  formatHostname($hostname)
|*  =========================
|*  This function interprets the gained hostname and
|*  converts the text color information from LfS
|*  (^1 = red, ^7 = white and so on) to valid HTML
|*  code to be shown on the website.
\***************************************************/
function formatHostname($hostname)
{
  if(strstr($hostname, "^")==FALSE) // If no ^ is found for color changing, just use the normal hostname
    $host = $hostname;
  else
  {
    $hostname_split = explode("^", $hostname);
    $i = 0;

    while($i < count($hostname_split))
    {
      if(strstr(substr($hostname, 0, strlen($hostname_split[$i])), "^")==TRUE)
      {
        $lfsCode = substr($hostname_split[$i], 0, 1);

        //$host .= "|lfsCode>"  . $lfsCode . "<lfsCode|"; // Line can be decommented to show, which LfS Codes are found

        $stringPart = substr($hostname_split[$i], 1, strlen($hostname_split[$i]));

        switch($lfsCode){
          case "0":   // BLACK
            $host = $host . "<font color=\"black\">" . $stringPart . "</font>";
            break;
          case "1":   // RED
            $host = $host . "<font color=\"red\">" . $stringPart . "</font>";
            break;
          case "2":   // GREEN
            $host = $host . "<font color=\"#7CFC00\">" . $stringPart . "</font>";
            break;
          case "3":   // YELLOW
            $host = $host . "<font color=\"yellow\">" . $stringPart . "</font>";
            break;
          case "4":   // BLUE
            $host = $host . "<font color=\"blue\">" . $stringPart . "</font>";
            break;
          case "5":   // MAGENTA
            $host = $host . "<font color=\"#FF00FF\">" . $stringPart . "</font>";
            break;
          case "6":   // CYAN
            $host = $host . "<font color=\"#00FFFF\">" . $stringPart . "</font>";
            break;
          case "7":   // WHITE
            $host = $host . "<font color=\"white\">" . $stringPart . "</font>";
            break;
          case "8":   // GREY
            $host = $host . "<font color=\"#cccccc\">" . $stringPart . "</font>";
            break;
          case "a": // ^a = *
            $host = $host . "*" . $stringPart;
            break;
          case "v": // ^v = |
            $racer = $racer . "|" . $stringPart;
            break;
          }
      }
      else
      {
        $host = $hostname_split[$i];
      }
      $host = "<font color=\"#cccccc\">" . $host . "</font>";
      $i++;
    }
  }
  return $host;
}


/***************************************************\
|*  get_trackName($track)
|*  =========================
|*  This function converts the short Track tag (e.g.
|*  BL1, KY2, SO2R and so on) to the complete name
|*  of the current track combination.
\***************************************************/
function getTrackName($track)
{

  switch($track) {
    ///////////////////////////
    // BLACKWOOD
    ///////////////////////////
    case ("BL1"):
      $trackName = "Blackwood GP";
      break;
    case ("BL1R"):
      $trackName = "Blackwood GP Rev.";
      break;
    case ("BL2"):
      $trackName = "Blackwood RallyX";
      break;
    case ("BL2R"):
      $trackName = "Blackwood RallyX Rev.";
      break;
    case ("BL3"):
      $trackName = "Blackwood Car Park";
      break;

    ///////////////////////////
    // SOUTH CITY
    ///////////////////////////
    case ("SO1"):
      $trackName = "South City Classic";
      break;
    case ("SO1R"):
      $trackName = "South City Classic Rev.";
      break;
    case ("SO2"):
      $trackName = "South City Sprint Track 1";
      break;
    case ("SO2R"):
      $trackName = "South City Sprint Track 1 Rev.";
      break;
    case ("SO3"):
      $trackName = "South City Sprint Track 2";
      break;
    case ("SO3R"):
      $trackName = "South City Sprint Track 2 Rev.";
      break;
    case ("SO4"):
      $trackName = "South City Long";
      break;
    case ("SO4R"):
      $trackName = "South City Long Rev.";
      break;
    case ("SO5"):
      $trackName = "South City Town Course";
      break;
    case ("SO5R"):
      $trackName = "South City Town Course Rev.";
      break;

    ///////////////////////////
    // FERN BAY
    ///////////////////////////
    case ("FE1"):
      $trackName = "Fern Bay Club";
      break;
    case ("FE1R"):
      $trackName = "Fern Bay Club Rev.";
      break;
    case ("FE2"):
      $trackName = "Fern Bay Green";
      break;
    case ("FE2R"):
      $trackName = "Fern Bay Green Rev.";
      break;
    case ("FE3"):
      $trackName = "Fern Bay Gold";
      break;
    case ("FE3R"):
      $trackName = "Fern Bay Gold Rev.";
      break;
    case ("FE4"):
      $trackName = "Fern Bay Black";
      break;
    case ("FE4R"):
      $trackName = "Fern Bay Black Rev.";
      break;
    case ("FE5"):
      $trackName = "Fern Bay Rallycross";
      break;
    case ("FE5R"):
      $trackName = "Fern Bay Rallycross Rev.";
      break;
    case ("FE6"):
      $trackName = "Fern Bay RallyX Green";
      break;
    case ("FE6R"):
      $trackName = "Fern Bay RallyX Green Rev.";
      break;

    ///////////////////////////
    // AUTOCROSS
    ///////////////////////////
    case ("AU1"):
      $trackName = "Autocross";
      break;
    case ("AU2"):
      $trackName = "Autocross Skid Pad";
      break;
    case ("AU3"):
      $trackName = "Autocross Drag Strip";
      break;
    case ("AU4"):
      $trackName = "Autocross 8 Lane Drag Strip";
      break;

    ///////////////////////////
    // KYOTO
    ///////////////////////////
    case ("KY1"):
      $trackName = "Kyoto Ring Oval";
      break;
    case ("KY1R"):
      $trackName = "Kyoto Ring Oval Rev.";
      break;
    case ("KY2"):
      $trackName = "Kyoto Ring National";
      break;
    case ("KY2R"):
      $trackName = "Kyoto Ring National Rev.";
      break;
    case ("KY3"):
      $trackName = "Kyoto Ring GP Long";
      break;

    case ("KY3R"):
      $trackName = "Kyoto Ring GP Long Rev.";
      break;

    ///////////////////////////
    // WESTHILL
    ///////////////////////////
    case ("WE1"):
      $trackName = "Westhill International";
      break;
    case ("WE1R"):
      $trackName = "Westhill International Rev.";
      break;

    ///////////////////////////
    // ASTON
    ///////////////////////////
    case ("AS1"):
      $trackName = "Aston Cadet";
      break;
    case ("AS1R"):
      $trackName = "Aston Cadet Rev.";
      break;
    case ("AS2"):
      $trackName = "Aston Club";
      break;
    case ("AS2R"):
      $trackName = "Aston Club Rev.";
      break;
    case ("AS3"):
      $trackName = "Aston National";
      break;
    case ("AS3R"):
      $trackName = "Aston National Rev.";
      break;
    case ("AS4"):
      $trackName = "Aston Historic";
      break;
    case ("AS4R"):
      $trackName = "Aston Historic Rev.";
      break;
    case ("AS5"):
      $trackName = "Aston Grand Prix";
      break;
    case ("AS5R"):
      $trackName = "Aston Grand Prix Rev.";
      break;
    case ("AS6"):
      $trackName = "Aston Grand Touring";
      break;
    case ("AS6R"):
      $trackName = "Aston Grand Touring Rev.";
      break;
    case ("AS7"):
      $trackName = "Aston North";
      break;
    case ("AS7R"):
      $trackName = "Aston North Rev.";
      break;
  }
  return $trackName;
}

/***************************************************\
|*  getIp ( )
|*  =========================
|*  This function returns the current IP address of the user
|*  and determines automatically if the user is behind a proxy.
\***************************************************/
function getIp() {
	if(getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
    else
        $ip = getenv("REMOTE_ADDR");
	return $ip;
}
?>