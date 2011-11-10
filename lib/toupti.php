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

require('middleware.php');
require('route.php');
require('request.php');
require('response.php');
require('model.php');
require('controller.php');
require('view.php');
  	
/**
 * @package Toupti
 */
class TouptiException extends Exception {}

/**
 * Toupti: yet another micro-framework made out of PHP.
 * @package Toupti
 * @author  Arnaud Berthomier <oz@cyprio.net>
 */
class Toupti extends Middleware
{
    /**
     * Parameters from _GET, _POST, and defined routes.
     */
    protected $params = array();

    /**
     * Request info
     */
    public $request = null;

    /**
     * Response info
     */
    public $response = null;

    /**
     *
     */
    public $controller = null;

    /**
     * The action we'll want to run...
     */
    public $action = null;

    /**
     * route key who solve the request.
     */
    public $path_key = null;
    
    /**
     * Routing setup
     */

    private static $_instance = null;

    /**
     * Toupti constructor
     * @todo throw TouptiException if not instance of Route (wrong path)
     */
    public function __construct($conf = array())
    {
    	//autoloader
    	spl_autoload_register(array($this,'autoload'));
    	
        parent::__construct($conf);
        
        if(isset($this->conf['route']))
        {
            include $this->conf['route'];
            $this->route = $route;
            // throw TouptiException if not instance of Route
            if(!($this->route instanceOf Route))
            {
                throw new TouptiException("Unable to load route from {$this->conf['route']}.");
            }
        } else {
            $this->route = new Route();
        }
    }

    public function get_params()
    {
        return $this->params;
    }

    /**
     * Dispatch browser query to the appropriate action.
     * This our "main" entry point.
     * @todo move $route params to the constructor.
     * @return void
     */
    public function run($req, $res)
    {
        $this->request = $req;
        $this->response = $res;

        // Find an action for the query, and set params accordingly.
        list($scheme, $params, $path_key) = $this->route->find_route($this->request->original_uri);

        // Update ourself
        $this->controller = $scheme['controller'];
        $this->action = isset($scheme['action']) ? $scheme['action'] : 'adefault';
        $this->path_key = $path_key;
        
        // cleaning params from controller and action;
        unset($params['action']);
        unset($params['controller']);

        // Merge route params with POST/GET/ressource values
        $params = array_merge($params, $this->request->post(), $this->request->get()); // FIXME Possible CSRF attacks here
        $this->request->params = $params;
        $controller_class = ucfirst($this->controller)."Controller";
        $this->call_action($controller_class, $this->action);
    }
    
	/**
     * Autoloader for Controllers and Models
     * @param $class
     */
	public static function autoload( $class ) {

		if (strpos($class, 'Controller') > 1)
        {
        	$file = dirname(dirname(__FILE__)) . '/modules/' . str_replace('_', DIRECTORY_SEPARATOR, $class . '.php');
	        if ( file_exists($file) ) {
	            require $file;
	        }
        }
		else
		{
		    $class = str_replace('Controller', '', $class);
		    $file = dirname(dirname(__FILE__)) . '/models/' . str_replace('_', DIRECTORY_SEPARATOR, strtolower($class) . '.php');
	        if ( file_exists($file) ) {
	            require $file;
	        }
		}
    }

    /**
     * Call a user action
     *
     * @param  string  $controller_name Name of the controller to call
     * @param  string  $method_name
     * @todo, moving this to middleware would be great.
     */
    private function call_action($controller_name, $method_name)
    {

    	//echo $controller_name;
    	//print_r(get_declared_classes());

        if($controller_name != 'Controller' && class_exists($controller_name, true))
        {
            Controller::setToupti($this);
            $controller = new $controller_name($this->request, $this->response);

            if(method_exists($controller, $method_name))
            {
                if($controller->isAuthorized($method_name))
                {
                    //$this->response->body = 
                    $controller->$method_name();
                    // \o/ good job, can exit now
                    return;
                }
                else
                {
                    throw new TouptiException('access_not_allowed', 403);
                }
            } else {
                throw new TouptiException('Route error. Action '. $method_name  .' not exist in '. $controller_name . ' for '. $this->request->original_uri . '.', 404);
            }
        }
        throw new TouptiException('Route error. Controller '. $controller_name . ' not found for '. $this->request->original_uri . '.', 404);
    }

}
