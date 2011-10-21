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
* Toupti Route
* @link    http://github.com/af83
* @link    http://dev.af83.com
* @copyright  af83
*
* @package Toupti
* @author AF83 Arnaud Berthommier, François de Metz, Gilles Robit, Luc-Pascal Ceccaldi, Ori Pekleman, Emmanuel Mazurier
 */

/**
 * @package Toupti
 */
class RouteException extends Exception {}

/**
 * @package Toupti
 */
class RouteNotFound extends RouteException {
	

}

/**
 * @package Toupti
 */
class Route
{

    private $app_root = null;
    private $_routes = array();

    public function __construct()
    {
    }

    /**
     * Add a new route to the internal dispatcher.
     * Will set an internal array (key: the route path, $params: the blue print, $config: other hard params for this root).
     * @param String $path Route path : a key from the user's routes
     * @param String $scheme
     */
    public function add($path, $scheme = null)
    {
        $route = array(
            'path'   => $path,
            'rx'     => '',
            'scheme' => $scheme);
        
        //print_r($scheme);

        // Escape path for rx
        $rx = str_replace('/', '\/', $path);

        // named path
        if ( strstr($path, ':') )
        {
            $matches = null;

            if ( preg_match_all('/:\w+/', $rx, $matches) )
            {
                foreach ( $matches[0] as $match )
                {
                    $group = isset($scheme[$match]) ? $scheme[$match] : '\w+';
                    $rx = preg_replace('/'.$match.'/', '('.$group.')', $rx);
                }
            }
        }

        // splat path
        if ( strstr($path, '*') )
        {
            $matches = null;

            if ( preg_match_all('/\*/', $rx, $matches) )
            {
                $rx = str_replace('*', '(.*)', $rx);
            }
        }

        $route['rx'] =  '/^\/' . $rx . '\/?$/';

        // Add new route
        $this->_routes[] = $route;
    }

    /**
     * Try to map browser request to one of the defined routes.
     * @params String path, query string ignored.
     * @return  Array  [0] => 'scheme', [1] => array( params... ), [2] => route_name
     * @todo check on interest of returning scheme.
     */
    public function find_route($path)
    {
        $found = false; 
        $scheme = null;
        $params = array();
        $route_path = null;

        // Get the query string without the eventual parameters passed.
        if ( $offset = strpos($path, '?') )
        {
            $path = substr($path, 0, $offset);
        }
        
        // Try each route
        foreach ( $this->_routes as $route )
        {
            $matches = array();

            // Found a match ?
            if ( preg_match($route['rx'], $path, $matches) )
            {
                $found = true;
                $params = array();
                $route_path = $route['path'];
                $scheme = $route['scheme'];
                if ( count($matches) > 1 )
                {
                    $params = $this->get_route_params($matches, $route);
                }
                $params = $this->merge($scheme, $params);
                break;
            }
        }
        if (!$found)
        {
            throw new RouteNotFound($path, '404');
        }
        return array($scheme, $params, $route_path);
    }

    /**
     * Extract params from the request with the corresponding path matches
     * @param   Array    $matches    preg_match $match array
     * @param   Array    $route      corresponding route array
     * @return  Array    Hash of request values, with param names as keys.
     */
    private function get_route_params($matches, $route)
    {
        $params      = array();
        $path_parts  = array();
        $param_count = 0;
        $path_array  = explode('/', $route['path']);

        // Handle each route modifier...
        foreach ( $path_array as $key => $param_name )
        {
            // Handle splat parameters (regexps like '.*')
            if ( substr($param_name, 0, 1) == '*' )
            {
                ++$param_count;
                if ( ! isset($params['splat']) ) $params['splat'] = array();
                $params['splat'] []= $matches[$param_count];
                continue;
            }

            // Don't treat non-parameters as parameters
            //@todo : check valid : if ( $param_name[0] != ":")
            if(substr($param_name, 0, 1) != ":")
            {
                continue;
            }

            // Extract param value
            ++$param_count;
            if ( isset($matches[$param_count]) )
            {
                $name = substr($param_name, 1, strlen($param_name));
                $params[$name] = $matches[$param_count];
            }

        }
        return $params;
    }

    /**
     * Merge params into scheme, removing ':' prefixed keys.
     * @params Array $scheme
     * @params Array $params
     * @return Array cleaned params
     */
    private function merge($scheme, $params)
    {
        if(is_null($scheme)) return $params;
        $params = array_merge($scheme, $params);
        $remove = array();
        foreach ($params as $key => $value)
        {
            // XXX is_integer is needed in case no regexp was set
            //     in $scheme when add() was called. :(
            if (is_integer($key) ||
                substr($key, 0, 1) == ':')
            {
                $remove[] = $key;
            }
        }
        foreach ($remove as $key) unset($params[$key]);
        return $params;
    }
}
