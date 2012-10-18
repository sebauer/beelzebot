beelzebot - An IRC bot for Live for Speed
=========================================

An IRC bot for Live for Speed building the bridge between LFS and IRC (!QuakeNet).

Requires a Live for Speed S2 server with enabled !InSim.

Supported Commands
------------------

### In IRC
 * [List of IRC Commands](https://github.com/sebauer/beelzebot/wiki/_pages)

For more information about IRC commands in Beelzebot, visit the [Commands](https://github.com/sebauer/beelzebot/wiki/IRC-Commands) page.

### In LFS
 * !irc `text` - sends a text message to IRC channel

## Configuration Options
This file needs to be saved as "config.php" in the root directory of lfs-irc!

## Enabling / Disabling Commands
See [Commands](https://github.com/sebauer/beelzebot/wiki/IRC-Commands).

## Enabling / Disabling !InSim Responders
InSimResponders are automatically loaded from the "insimresponders" directory. To disable specific responders, simply rename the files to `_InSimResponderXXX.php` or remove the file from the directory.
