<?php
/**
 * Fake socket
 * @package Toupti
 */
class TouptiSocket
{
    public function __construct($url, $encoding, $toupticonf)
    {
        $this->url = $url;
        $this->encoding = $encoding;
        $this->toupticonf = $toupticonf;
        
        $this->setRequest();
        $this->setResponse();
        $this->setApp();

        $this->app->run($this->request, $this->response);
        $this->first = true;
    }

    public function setRequest()
    {

        $_SERVER  = array();
        $_GET     = array();
        $_POST    = array();
        $_REQUEST = array();
        $_FILES   = array();

        if ($this->encoding->getMethod() != 'POST')
        {
            $this->fillGet($this->encoding);
        }
        else
        {
            $this->fillPost($this->encoding);
            $this->fillGet($this->url->_request);
        }
        $url = $this->url->asString();
        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['REQUEST_METHOD'] = $this->encoding->getMethod();
        
        $this->request = new RequestMapper();
    }

    public function setResponse()
    {
        $this->response = new TouptiResponse();
    }

    public function setApp()
    {
        View::reset();
        View::useLib('MockTest');
        View::conf($this->toupticonf['viewConf']);
        MocktestView::useLib($this->toupticonf['view'].'View');

        Controller::setResponse($this->response);
        Controller::setRequest($this->request);
        
        $this->app = new MiddlewareStack();
        $this->toupti = new Toupti($this->toupticonf['toupti']);
        $this->app->add($this->toupti);

    }

    protected function fillGet($encoding)
    {
        foreach ($encoding->getAll() as $pair)
        {
            $_GET[$pair->getKey()] = $pair->getValue();
        }
    }

    protected function fillPost($encoding)
    {
        foreach ($encoding->getAll() as $pair)
        {
            $_POST[$pair->getKey()] = $pair->getValue();
        }
        $this->handleFiles($encoding->getAll());
    }

    public function isError()
    {
        return false;
    }
    /**
     * TODO: implement this
     */
    public function getSent()
    {
        return '';
    }

    public function read()
    {
        if ($this->first)
        {
            $this->first = false;
            $headers = $this->response->get_headers();
            $content = 'HTTP/1.1 200 OK'."\r\n";
            if (isset($headers['Status']))
            {
                $content = $headers['Status'] . "\r\n";
                unset($headers['Status']);
            }
            foreach ($headers as $n => $v)
            {
                $content .= $n . ': '. $v . "\r\n";
            }
            return $content ."\r\n" . ($this->response->body instanceOf View ? $this->response->body->fetch() : $this->response->body);
        }
        return '';
    }

    public function getTouptiResponse()
    {
        return $this->response;
    }

    private function handleFiles($encoding)
    {
        foreach ($encoding as $pair)
        {
            $key = $pair->getKey();
            $p = $pair->getValue();
            $matches = array();
            if (preg_match('/^([\w]+)\[([0-9]+)\]/', $key, $matches) && is_resource($p))
            {
                $key   = $matches[1];
                $i     = $matches[2];
                if (!isset($_FILES[$key]))
                    $_FILES[$key] = array('name' => array(), 'size' => array(), 'type' => array(), 'error' => array(), 'tmp_name' => array());
                $file = $this->prepareUploadFile($p);
                $_FILES[$key]['name'][$i]     = $file['name'];
                $_FILES[$key]['size'][$i]     = $file['size'];
                $_FILES[$key]['type'][$i]     = $file['type'];
                $_FILES[$key]['tmp_name'][$i] = $file['tmp_name'];
                $_FILES[$key]['error'][$i]    = $file['error'];
            }
            elseif (is_resource($p))
            {
                $_FILES[$key] = $this->prepareUploadFile($p);
            }
        }
    }

    private function prepareUploadFile($resource)
    {
        $meta_data = stream_get_meta_data($resource);
        $uri = $meta_data['uri'];
        $file = array();
        $file['name'] = basename($uri);
        $file['size'] = filesize($uri);
        $file['type'] = mime_content_type($uri);
        $tmp = tempnam(sys_get_temp_dir(), basename($uri));
        if (!copy($uri, $tmp))
        {
            throw new Exception('cannot copy '. $uri .' to '. $tmp);
        }
        $file['tmp_name'] = $tmp;
        $file['error'] = UPLOAD_ERR_OK;
        return $file;
    }
}
/**
 * @package Toupti
 */
class TouptiUserAgent
{
    public function __construct($toupticonf)
    {
        $this->toupticonf = $toupticonf;
    }

    public function useProxy($proxy, $username, $password)
    {
        return;
    }

    public function fetchResponse($url, $encoding)
    {
        if ($encoding->getMethod() != 'POST') {
            $url->addRequestParameters($encoding);
        }
        return $this->createResponse($url, $encoding);
    }

    protected function createResponse($url, $encoding)
    {
        $this->socket = new TouptiSocket($url, $encoding, $this->toupticonf);
        $response = new SimpleHttpResponse(
                                           $this->socket,
                                           $url,
                                           $encoding);
        return $response;
    }

    public function getTouptiResponse()
    {
        return $this->socket->getTouptiResponse();
    }
}

class TouptiBrowser extends SimpleBrowser
{
    protected $response = null;

    protected $currenturl = null;

    public function __construct($conf)
    {
        $this->toupticonf = $conf;
        parent::__construct();
    }

    public function _createuserAgent()
    {
        return new TouptiUserAgent($this->toupticonf);
    }

    public function getTouptiResponse()
    {
        return $this->_user_agent->getTouptiResponse();
    }
}

/**
 * WebTestCase for testing toupti application
 * @package Toupti
 */
abstract class TouptiTestCase extends WebTestCase
{
    protected $_testcase = null;
    public function createBrowser()
    {
        return new TouptiBrowser($this->touptiConf());
    }

    abstract function touptiConf();

    public function getTouptiResponse()
    {
        return $this->getBrowser()->getTouptiResponse();
    }

    protected function getTestCase()
    {
        if (is_null($this->_testcase))
        {
            $test = new UnitTestCase(get_class($this));
            $test->_reporter = $this->_reporter;
            $this->_testcase = $test;
        }
        return $this->_testcase;
    }

    public function __call($name, $arguments)
    {
        call_user_func_array(array($this->getTestCase(), $name), $arguments);
    }
}
