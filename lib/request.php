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
* Toupti Request Mapper
* @link    http://github.com/af83
* @link    http://dev.af83.com
* @copyright  af83
*
* @package Toupti
* @author AF83 Arnaud Berthommier, François de Metz, Gilles Robit, Luc-Pascal Ceccaldi, Ori Pekleman, Emmanuel Mazurier
*/
class Request
{
    
    /**
     * Http Method
     * @var string 
     */
    
    public $method;
    /**
     *HTTP Accept headers
     * @var string 
     */
    
    public $accept;
    /**
     * Original request URI
     * @var string 
     */
    
    public $original_uri;

    /**
     * Resource URI (excluding the application directory path)
     * @var string 
     */
    public $resource;

    /**
     * Host name (excluding the application directory path)
     * @var string 
     */
    public $host;
    
    /**
     * @var array GET paramters' names and values
     */
    private $get;
    
    /**
     * @var array POST paramters' names and values
     */
    private $post;

    /**
     * @var array PUT paramters' names and values
     */
    private $put;

    /**
     * @var string Raw request input (for POST and PUT requests)
     */
    private $input;
    
    /**
     *
     * @var array Request Headers
     */
    protected $headers;
    
    /**
     * is the request an HTTP GET request
     * @var boolean 
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
     */
    private $isGet = false;
    
    /**
     * is the request an HTTP HEAD request
     * @var boolean 
     */
    private $isHead = false;
    
    /**
     *  the request an HTTP POST request
     * @var boolean is
     */
    private $isPost = false;
    
    /**
     * is the request an HTTP PUT request
     * @var boolean
     */
    private $isPut = false;
    
    /**
     * is the request an HTTP DELETE request
     * @var boolean
     */
    private $isDelete = false;
    
    /**
     * is the request an HTTP OPTIONS request
     * @var boolean
     */
    private $isOptions = false;
    
    /**
     * is the request an HTTP TRACE request
     * @var boolean
     */
    private $isTrace = false;
    
    /**
     * is the request an HTTP CONNECT request
     * @var boolean
     */
    private $isConnect = false;

    /**
     * @var array possible request methods
     */
    private $possible_request_methods = array('GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'TRACE', 'CONNECT');

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setRequestMethod($_SERVER['REQUEST_METHOD']);
        $this->accept       = $this->parseAcceptHeaders(isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : NULL);
        $this->headers      = $this->getRequestHeaders();
        $this->original_uri = $_SERVER['REQUEST_URI'];

        $this->resource = $this->extractQueryString();
        $this->get =  $_GET;
        $this->post =   $_POST;
        if ( $this->method === "PUT" ) {
            $this->input = file_get_contents('php://input');
            $this->put =  $this->getPutParameters();
        }
        $this->cookies =   $_COOKIE;
        $this->checkForHttpMethodOverride();
    }
    /**
     * Get Request headers, normalize between apache and FastCGI
     *
     * @return array of request headers
     */
    protected function getRequestHeaders()
    {
        if (function_exists('apache_request_headers'))
        {
            return $this->getApacheRequestHeaders();
        }
        return $this->getFastCgiRequestHeaders();
    }

    /**
     * Get Request headers, normalize between apache and FastCGI
     *
     * @return array of request headers
     */
    protected function getRequestHost()
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_HOST"]))
        {
            $vhost = $_SERVER["HTTP_X_FORWARDED_HOST"];
        }
        else
        {
            if (isset($_SERVER["HTTP_HOST"]))
            {
                $vhost = $_SERVER["HTTP_HOST"];
            }
            else
            {
                $vhost = $_SERVER["SERVER_NAME"];
            }
        }
        return $vhost;
    }


    /**
     * Get Request Apache request headers
     *
     * @return array of request headers
     */
    protected function getApacheRequestHeaders()
    {
        return apache_request_headers();
    }

    /**
     * Get FastCGI request headers
     *
     * @return array of request headers
     */
    protected function getFastCgiRequestHeaders()
    {
        $ret = array();
        foreach($_SERVER as $key => $value)
        {
            $matches = array();
            if(preg_match('/^HTTP_(.+)$/', $key, $matches))
            {
                $key = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower($matches[1]))));
                $ret[$key] = $value;
            }
        }
        return $ret;
    }

    /**
     * Magic To string method, returning  three lines of text represing the called url, the method and the accept content type header.
     *
     * @example $r = new Request; echo $r; will return something like:
     * url = http://dev.af83.com
     * method = GET
     * accept = text/html
     *
     * @todo Please refactor this, or document why this is a good idea
     * @fixme Anyway the $this->url is not set anywhere. Because of the quite horrible magic _get nothing will ever raise an error on accessors on this class
     * @fixme Anyway accept is now an array so this is really useless, changing to an implode
     * @return  string
     */
    public function __toString() {
        return
            "resource = [{$this->resource}]\n".
            "method = [{$this->method}]\n".
            "accept = [".implode(',',$this->accept)."]\n";
    }

    /**
     * Set which method is used. Sets private variable such as isGet to true when method is matched (magic method isGet() can also be called)
     * return void
     * @fixme should we unset a previously set method in case we are overriding?
     * @fixme why are there three methods  for this (magic getter, public properties and method property????)
     */
    private function setRequestMethod($request_method)
    {
        if (in_array($request_method, $this->possible_request_methods))
        {   
            $this->method = $request_method;
            $method = "is" . ucfirst(strtolower($request_method));
            $this->$method = true;
        }
    }



    /**
     * Returns possible request methods
     *
     * @return array  Possible request methods
     */
    public function getPossibleRequestMethods()
    {
        return $this->possible_request_methods;
    }

    /**
     * Returns current HTTP Request Method ()
     *
     * @return String Request Method
     */
    public function getRequestMethod()
    {
        foreach($this->possible_request_methods as $request_method)
        {
            $method = "is".ucfirst(strtolower($request_method));
            if($this->$method) return $request_method;
        }
    }

    /**
     * Returns a specific request header or null
     *
     * @param string $header 
     * @return String Request Header
     */
    public function getHeader($header)
    {
        return isset($this->headers[$header]) ? $this->headers[$header] : NULL;
    }

    /**
     * Returns AJAX context true if called with ajax
     *
     * @return Boolean
     */
    public function isXHR()
    {
        if (array_key_exists('X-Requested-With', $this->headers) && $this->headers['X-Requested-With'] == 'XMLHttpRequest')
            return true;
        // ie bug ?
        elseif (array_key_exists('x-requested-with', $this->headers) && $this->headers['x-requested-with'] == 'XMLHttpRequest')
            return true;
        return false;
    }

    /**
     * method is get
     * @return Boolean
     */
    public function isGet()
    {
        return $this->isGet;
    }

    /**
     *  method is post
     * @return Boolean
     */
    public function isPost()
    {
        return $this->isPost;
    }

    /**
     * method is put
     * @return Boolean
     */
    public function isPut()
    {
        return $this->isPut;
    }

    /**
     * method is head
     * @return Boolean
     */
    public function isHead()
    {
        return $this->isHead;
    }

    /**
     * method is delete
     * @return Boolean
     */
    public function isDelete()
    {
        return $this->isDelete;
    }

    /**
     * method is options
     * @return Boolean
     */
    public function isOptions()
    {
        return $this->isOptions;
    }

    /**
     * method is trace
     * @return Boolean
     */
    public function isTrace()
    {
        return $this->isTrace;
    }

    /**
     * method is connect
     * @return Boolean
     */
    public function isConnect()
    {
        return $this->isConnect;
    }

    /**
     * Parses the accept (content type) headers from the HTTP Request, defaults to text/html in a very convulted manner
     *
     * @param string $accept_header 
     * @param string $default 
     * @return array of accepted content type by priority
     * @author Ori Pekelman
     */
    private function parseAcceptHeaders($accept_header, $default="text/html")
    {
        //@fixme: why a closed list ? http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html at least add text/plain and json http://www.json.org/JSONRequest.html
        $formats = array(
            'text/html' => 'html',
            'application/xhtml+xml' => 0,
            'application/xml' => 0,
            '*/*' => 'html',
        );

        $accept = array();
        $headers = explode(',', $accept_header);
        foreach ($headers as $header){
            list($mime, $q) =  strpos($header,';q=') ? explode(';q=', $header): array($header,'1'); // As per http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html default q value is 1
            $accept[$mime] = ($q === null)? 1 : $q;
        }

        arsort($accept);
        $accept[] = $default;

        foreach ($accept as $format => $q){
            if (isset($formats[$format]))
                break;
        }


        //weird ie6 bug sometimes doesn't send headers so fighing notice here
        //@todo verify this added isset() maybe changing logic. Anyway $formats[$format] would basically always be false for application/xhtml and application/xml see the formats array. This is probably an oversight
        if ($format && isset($formats[$format]))   return array($format, $formats[$format]);   
    }

    /**
     * Add a PUT|POST|GET parameter
     *
     * Allow to add a parameter to a PUT POST or GET request
     * PUT, POST, or GET parameter (searched in that order).
     * if the para
     * @param  string    $method (GET / PUT  / POST)
     * @param  string    $key The paramter name
     * @param  string    $value The paramter value to be set
     * @return   void
             */

            public function addParam ($method, $key, $value ) {
                $method=strtolower($method);

                if ( is_null($this->params($key))) {
                    switch ($method){
                    case "get": $this->get[$key]=$value ; break;
                    case "post": $this->post[$key]=$value ;break;
                    case "put": $this->put[$key]=$value ;break;
                    case "delete": $this->get[$key]=$value ; ;break; // should we do this. Or should we call "GET" params something else like "URL" params. This seems like a very bad habit from PHP $_GET
                    }
                };
            }

        /**
         * Fetch PUT|POST|GET parameter
         *
         * Allow to remove a parameter from a PUT POST or GET request
         * PUT, POST, or GET parameter (searched in that order).
         * 
         * @param  string    $key The paramter name
         * @return   string|null
             */
            public function removeParam ($key ) {
                if ( isset($this->put[$key]) ) {
                    unset ($this->put[$key]);
                }
                if ( isset($this->post[$key]) ) {
                    unset ($this->put[$key]);
                }
                if ( isset($this->get[$key]) ) {
                    unset ($this->put[$key]);
                }
            }

        /* from slim*/


        /***** PARAM ACCESSORS *****/

        /**
         * Fetch PUT|POST|GET parameter
         *
         * This is the preferred method to fetch the value of a
         * PUT, POST, or GET parameter (searched in that order).
         * 
         * @param  string    $key The paramter name
         * @return   string|null
             */
            public function params( $key ) {
                if ( isset($this->put[$key]) ) {
                    return $this->put[$key];
                }
                if ( isset($this->post[$key]) ) {
                    return $this->post[$key];
                }
                if ( isset($this->get[$key]) ) {
                    return $this->get[$key];
                }
                return null;
            }

        /**
         * Fetch GET parameter(s)
         *
         * @param  string        $key  Name of parameter
         * @return   array|string|null      All parameters, or parameter value if $key provided.
             */
            public function get( $key = null ) {
                if ( is_null($key) ) {
                    return $this->get;
                }
                return ( isset($this->get[$key]) ) ? $this->get[$key] : null;
            }

        /**
         * Fetch POST parameter(s)
         *
         * @param  string        $key  Name of parameter
         * @return   array|string|null      All parameters, or parameter value if $key provided.
             */
            public function post( $key = null ) {
                if ( is_null($key) ) {
                    return $this->post;
                }
                return ( isset($this->post[$key]) ) ? $this->post[$key] : null;
            }

        /**
         * Fetch PUT parameter(s)
         *
         * @param  string        $key  Name of parameter
         * @return   array|string|null      All parameters, or parameter value if $key provided.
             */
            public function put( $key = null ) {
                if ( is_null($key) ) {
                    return $this->put;
                }
                return ( isset($this->put[$key]) ) ? $this->put[$key] : null;
            }

        /**
         * Fetch COOKIE value
         *
         * @param  string    $name   The cookie name
         * @return   string|null      The cookie value, or NULL if cookie not set
             */
            public function cookie( $name ) {
                return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
            }
        /***** HELPERS *****/

        /**
         * Extract Resource URL
         *
         * This method converts the raw HTTP request URL into the desired
         * resource string, excluding the path to the root Slim app directory
         * and any query string.
         *
         * @author  Kris Jordan <http://www.github.com/KrisJordan>
         * @return   string The resource URI
             */
            private function extractQueryString() {
                //Get application base URI path (no trailing slash)
                $this->root = rtrim(dirname($_SERVER['PHP_SELF']), '/');

                //Get the application-specific URI path
                if ( !empty($_SERVER['PATH_INFO']) ) {
                    $uri = $_SERVER['PATH_INFO'];
                } else {
                    if ( isset($_SERVER['REQUEST_URI']) ) {
                        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                        $uri = rawurldecode($uri);
                    } else if ( isset($_SERVER['PHP_SELF']) ) {
                        $uri = $_SERVER['PHP_SELF'];
                    } else {
                        return null;
                        //        throw new RuntimeException('Unable to detect request URI');
                    }
                }

                //Remove application base URI path from the application-specific URI path
                if ( $this->root !== '' && strpos($uri, $this->root) === 0 ) {
                    $uri = substr($uri, strlen($this->root));
                }

                return $uri;
            }

        /**
         * Fetch and parse raw POST or PUT paramters
         *
         * @author  Kris Jordan <http://www.github.com/KrisJordan>
         * @return string
             */
            private function getPutParameters() {
                $putdata = $this->input; //@fixme this was an error using $this->input need to commit upstream
                if ( function_exists('mb_parse_str') ) {
                    mb_parse_str($putdata, $outputdata);
                } else {
                    parse_str($putdata, $outputdata);
                }
                return $outputdata;
            }

        /**
         * Fetch HTTP request headers
         *
         * @author  Kris Jordan <http://www.github.com/KrisJordan>
         * @return array
             */
            private function getHttpHeaders() {
                $httpHeaders = array();
                foreach ( array_keys($_SERVER) as $key ) {
                    if ( substr($key, 0, 5) === 'HTTP_' ) {
                        $httpHeaders[substr($key, 5)] = $_SERVER[$key];
                    }
                }
                return $httpHeaders;
            }


        /**
         * Check for HTTP request method override
         *
         * Because traditional web browsers do not support PUT and DELETE
         * HTTP methods, we must use a hidden form input field to
         * mimic PUT and DELETE requests. We check for this override here.
         *
         * @author  Kris Jordan <http://www.github.com/KrisJordan>
         * @return void
             */
            private function checkForHttpMethodOverride() {
                if ( array_key_exists('_METHOD', $this->post) ) {
                    $this->setRequestMethod($this->post['_METHOD']);
                    unset($this->post['_METHOD']);
                    if ( $this->method === "PUT" ) {
                        $this->put = $this->post;
                    }

                }
            }

}

class RequestMapper extends Request {} // Compatability with earlier Toupti
