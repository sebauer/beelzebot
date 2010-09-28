<?php
/**
 * NEVER CHANGE ANYTHING IN THIS CONFIGURATION!
 *
 * DO **NOT** TOUCH ANY OF THE DEFINES!
 *
 */
set_time_limit (0);

require_once('include/autoload.php');
require_once('config.php');
require_once('include/functions.inc.php');

if (!defined('_REVISION')) {
	if (file_exists('.svn' . DIRECTORY_SEPARATOR. 'entries')) {
		$svn = file('.svn' . DIRECTORY_SEPARATOR . 'entries');
		if (is_numeric(trim($svn[3]))) {
			$version = $svn[3];
		} else { // pre 1.4 svn used xml for this file
			$version = explode('"', $svn[4]);
			$version = $version[1];
		}
		define ('_REVISION', trim($version));
		unset ($svn);
		unset ($version);
	} else {
		define ('_REVISION', 0); // default if no svn data avilable
	}
}