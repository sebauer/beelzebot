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

class InSimResponderNCN extends aInSimResponder {

    public function isResponsible($packet){
        if($packet[1] != pack("C", ISP_NCN)) return false;
        return true;
    }

    public function handleCall($packet, InSim $insim, Bot $bot){
        $bot->log("Received ISP_NCN...");
        
        $byte_packed = unpack("c", substr($packet, 3, 1)); // byte
        $uid = $byte_packed[1];
        
        $bot->log("New connection with UID $uid..");
        
        $username = trim(substr($packet, 4, 24));
        $nickname = trim(substr($packet, 28, 24));
        
        $insim->addConnection($uid, $username, $nickname);
    }
}