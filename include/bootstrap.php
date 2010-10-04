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

/**
 * NEVER CHANGE ANYTHING IN THIS CONFIGURATION!
 *
 * DO **NOT** TOUCH ANY OF THE DEFINES!
 *
 */
set_time_limit (0);

define('INSIM_KEEPALIVE', 30);

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