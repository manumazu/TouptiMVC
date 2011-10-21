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
* Toupti View
* @link    http://github.com/af83
* @link    http://dev.af83.com
* @copyright  af83
*
* @package Toupti
* @author AF83 Arnaud Berthommier, François de Metz, Gilles Robit, Luc-Pascal Ceccaldi, Ori Pekleman
*/
class View
{
    //protected static $view_class = NULL;
    public static $view_class = NULL;

    protected $view_object = NULL;

    public static $conf = array(
    );

    public $_js = array();
    
    public static function conf($conf)
    {
        self::$conf = $conf;
    }

    public function reset()
    {
        self::$view_class = NULL;
        self::$conf = NULL;
    }

    public static function useLib($lib)
    {
        $class_name = ucfirst(strtolower($lib)).'View';
        if (!class_exists($class_name, true))
        {
            throw new TouptiException(sprintf("The %s view adaptor could not be loaded, class name %s", $lib, $class_name));
        }
        self::$view_class = $class_name;
    }

    public function __construct($tpl = '', $params = array())
    {
        $view_class = self::$view_class;
        if (is_null($view_class))
        {
            throw new TouptiException("no adaptor set");
        }
        call_user_func_array(array($view_class, 'conf'), array(self::$conf));

        $this->view_object = new $view_class($tpl, $params);
    }

    public function __call($name, $arguments)
    {
        if (is_null($this->view_object) || !method_exists($this->view_object, $name))
        {
            throw new TouptiException(sprintf("Could not call %s either on View nor on a Lib", $name));
        }
        return call_user_func_array(array($this->view_object, $name), $arguments);
    }

    /**
     * Required javascript file.
     * @param $files String  needed javascript file.
     */
    public function js()
    {
        $args = (func_num_args() == 1 && is_array(func_get_arg(0))) ? func_get_arg(0) : func_get_args();
        $this->_js = array_merge($this->_js, $args);
    }
}
