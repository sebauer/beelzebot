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
 * This file contains all defines for InSim packages, they can be used as enum
 * and are required by the InSim class.
 */



define('TINY_NONE', 0);			//  0					: see "maintaining the connection"
define('TINY_VER', 1);			//  1 - info request	: get version
define('TINY_CLOSE', 2);			//  2 - instruction		: close insim
define('TINY_PING', 3);			//  3 - ping request	: external progam requesting a reply
define('TINY_REPLY', 4);			//  4 - ping reply		: reply to a ping request
define('TINY_VTC', 5);			//  5 - info			: vote cancelled
define('TINY_SCP', 6);			//  6 - info request	: send camera pos
define('TINY_SST', 7);			//  7 - info request	: send state info
define('TINY_GTH', 8);			//  8 - info request	: get time in hundredths (i.e. SMALL_RTP)
define('TINY_MPE', 9);			//  9 - info			: multi player end
define('TINY_ISM', 10);			// 10 - info request	: get multiplayer info (i.e. ISP_ISM)
define('TINY_REN', 11);			// 11 - info			: race end (return to game setup screen)
define('TINY_CLR', 12);			// 12 - info			: all players cleared from race
define('TINY_NCN', 13);			// 13 - info			: get all connections
define('TINY_NPL', 14);			// 14 - info			: get all players
define('TINY_RES', 15);			// 15 - info			: get all results
define('TINY_NLP', 16);			// 16 - info request	: send an IS_NLP packet
define('TINY_MCI', 17);			// 17 - info request	: send an IS_MCI packet
define('TINY_REO', 18);			// 18 - info request	: send an IS_REO packet
define('TINY_RST', 19);			// 19 - info request	: send an IS_RST packet

define('ISP_NONE', 0);	 		//  0					: not used
define('ISP_ISI', 1);	 		//  1 - instruction		: insim initialise
define('ISP_VER', 2);	 		//  2 - info			: version info
define('ISP_TINY', 3);	 		//  3 - both ways		: multi purpose
define('ISP_SMALL', 4);	 		//  4 - both ways		: multi purpose
define('ISP_STA', 5);	 		//  5 - info			: state info
define('ISP_SCH', 6);	 		//  6 - instruction		: single character
define('ISP_SFP', 7);	 		//  7 - instruction		: state flags pack
define('ISP_SCC', 8);	 		//  8 - instruction		: set car camera
define('ISP_CPP', 9);	 		//  9 - both ways		: cam pos pack
define('ISP_ISM', 10);	 		// 10 - info			: start multiplayer
define('ISP_MSO', 11);	 		// 11 - info			: message out
define('ISP_III', 12);	 		// 12 - info			: hidden /i message
define('ISP_MST', 13);	 		// 13 - instruction		: type message or /command
define('ISP_MTC', 14);	 		// 14 - instruction		: message to a connection
define('ISP_MOD', 15);	 		// 15 - instruction		: set screen mode
define('ISP_VTN', 16);	 		// 16 - info			: vote notification
define('ISP_RST', 17);	 		// 17 - info			: new connection
define('ISP_NCN', 18);	 		// 18 - info			: new connection
define('ISP_CNL', 19);	 		// 19 - info			: connection left
define('ISP_CPR', 20);	 		// 20 - info			: connection renamed
define('ISP_NPL', 21);	 		// 21 - info			: new player (joined race)
define('ISP_PLP', 22);	 		// 22 - info			: player pit (keeps slot in race)
define('ISP_PLL', 23);	 		// 23 - info			: player leave (spectate - loses slot)
define('ISP_LAP', 24);	 		// 24 - info			: lap time
define('ISP_SPX', 25);	 		// 25 - info			: split x time
define('ISP_PIT', 26);	 		// 26 - info			: pit stop start
define('ISP_PSF', 27);	 		// 27 - info			: pit stop finish
define('ISP_PLA', 28);	 		// 28 - info			: pit lane enter / leave
define('ISP_CCH', 29);	 		// 29 - info			: camera changed
define('ISP_PEN', 30);	 		// 30 - info			: penalty given or cleared
define('ISP_TOC', 31);	 		// 31 - info			: take over car
define('ISP_FLG', 32);	 		// 32 - info			: flag (yellow or blue)
define('ISP_PFL', 33);	 		// 33 - info			: player flags (help flags)
define('ISP_FIN', 34);	 		// 34 - info			: finished race
define('ISP_RES', 35);	 		// 35 - info			: result confirmed
define('ISP_REO', 36);	 		// 36 - both ways		: reorder (info or instruction)
define('ISP_NLP', 37);	 		// 37 - info			: node and lap packet
define('ISP_MCI', 38);	 		// 38 - info			: multi car info
define('ISP_MSX', 39);	 		// 39 - instruction 	: type message (for long messages)
define('ISP_MSL', 40);	 		// 40 - instruction		: message to local computer

?>