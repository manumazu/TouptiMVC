<?php
/* Copyright (c) 2009, Arnaud Berthomier
* Copyright (c) 2009-2010, AF83
* All rights reserved.
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions are met:
*
*     * Redistributions of source code must retain the above copyright
*       notice, this list of conditions and the following disclaimer.
*     * Redistributions in binary form must reproduce the above copyright
*       notice, this list of conditions and the following disclaimer in the
*       documentation and/or other materials provided with the distribution.
*     * Neither the name of the University of California, Berkeley nor the
*       names of its contributors may be used to endorse or promote products
*       derived from this software without specific prior written permission.
*
* THIS SOFTWARE IS PROVIDED BY THE AUTHORS AND CONTRIBUTORS ``AS IS'' AND ANY
* EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
* WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
* DISCLAIMED. IN NO EVENT SHALL THE AUTHORS AND CONTRIBUTORS BE LIABLE FOR ANY
* DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
* (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
* LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
* ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
* SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

/**
* Toupti View Adaptor
* @link    http://github.com/af83
* @link    http://dev.af83.com
* @copyright  af83
*
* @package Toupti
*/
abstract class ViewAdaptor
{
    private $notifs = array();

    abstract public static function conf($conf); 

    abstract public function __construct($tpl = '', $params = array());

    abstract public function assign($key, $value);

    abstract public function get($key = null);

    abstract public function display($tpl = null);

    abstract public function fetch($tpl = null);

    abstract public function compile($tpl = null);

    /**
     * @return Array notifs accumulator.
     */
    public function getNotifs()
    {
        return $this->notifs;
    }

    /**
     * @param Mixed $notify notification or an array of them.
     */
    public function notify($notify)
    {
        if(is_array($notify))
        {
            foreach($notify as $key => $value)
                $this->addNotify($value);
        }
        else
        {
            $this->addNotify($notify);
        }
    }

    private function addNotify($notify)
    {
        if (!is_null($notify))
            $this->notifs []= $notify;
    }
}
