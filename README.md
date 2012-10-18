beelzebot - An IRC bot for Live for Speed
=========================================

An IRC bot for Live for Speed building the bridge between LFS and IRC (!QuakeNet).

Requires a Live for Speed S2 server with enabled !InSim.

Supported Commands
------------------

### In IRC
 * [List of IRC Commands](http://code.google.com/p/beelzebot/w/list?can=2&q=label:CommandsIRC&sort=pagename&colspec=PageName%20Summary%20Changed%20ChangedBy)

For more information about IRC commands in Beelzebot, visit the [Commands] page.

### In LFS
 * !irc `text` - sends a text message to IRC channel

## Configuration Options
This file needs to be saved as "config.php" in the root directory of lfs-irc!
{{{
<?php
define('USERNAME', 'octbot'); // Username of the Bot used for auth and as nickname
define('PASSWORD', 'secretpassword'); // Password for authing the bot
define('CHANNEL', '#octbottest'); // Channel to join
define('SERVER', 'clanserver4u.de.quakenet.org'); // IRC Server to use
define('IRC_PORT', 6668); // Port of the IRC Server
define('INSIM_SERVER', 'localhost'); // Hostname / IP of the InSim Server
define('INSIM_PORT', 65302); // Port for InSim
define('INSIM_PASS', 'foobar123'); // Password for InSim
define('LFSHOST', 'Our Public Server'); // Server name of the LFS Server, required for checking LFS World

define('LFS_RANDOM_URL', 'http://somedomain.net/randomlfs/random.php'); // An publically accessible URL on which the Random LFS script can be found
define('TOPIC_TEMPLATE', 'IMBA RACING - Users online on Server: %d'); // Template used for updating the topic
?>
}}}

## Enabling / Disabling Commands
See [Commands].

## Enabling / Disabling !InSim Responders
InSimResponders are automatically loaded from the "insimresponders" directory. To disable specific responders, simply rename the files to `_InSimResponderXXX.php` or remove the file from the directory.
