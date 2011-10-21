<?php
/* Copyright (c) 2010, Gilles Robit
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
 * Middleware for toupti, must implement run() method.
 * @package Toupti
 * @author Gilles Robit <gilles.robit@af83.com>
 */
abstract class Middleware
{
    /**
     * Current config
     */
    public $conf = null;
    private $next_middleware = null;

    /**
     * Middleware constructor
     * @param Array $conf
     */
    public function __construct($conf = array())
    {
        $this->conf = $conf;
    }
    
    public function setNextMiddleware(Middleware $next_middleware)
    {
        $this->next_middleware = $next_middleware;
    }

    /**
     * Doing the middle ware stuff.
     * Use $this->follow($req, $res) for next middleware in stack.
     */
    abstract public function run($req, $res);

    /**
     * firing next middleware
     */
    protected function follow($req, $res) {
        if(is_null($this->next_middleware))
        {
            return false;
        }
        $this->next_middleware->run($req, $res);
        return true;
    }
}

class MiddlewareStack
{
    private $stack = array();
    
    public function __construct()
    {

    }

    public function add(Middleware $middleware)
    {
        if(count($this->stack) > 0)
        {
            $this->stack[sizeof($this->stack) - 1]->setNextMiddleware($middleware);
        }
        $this->stack[] = $middleware;
    }

    function getStack()
    {
        return $this->stack;
    }

    /**
     * replace a middleware by another.
     */
    function replace($old, $new)
    {
        foreach($this->stack as $key => $middleware)
        {
            if($middleware == $old)
            {
                //there is a previous
                if($key > 0)
                {
                    $this->stack[$key - 1]->setNextMiddleware($new);
                }
                $this->stack[$key] = $new;
                //there is a follower
                if($key < sizeof($this->stack) - 1)
                {
                    $new->setNextMiddleware($this->stack[$key + 1]);
                }
            }
        }
    }

    public function run($req = null, $res = null)
    {
        if(count($this->stack) > 0 )
        {
            $this->stack[0]->run($req, $res);
        } else
        {
            throw new MiddlewareStackException("MiddleWareStack can't be run when stack is empty");
        }
    }
}

class MiddlewareStackException extends Exception {};
