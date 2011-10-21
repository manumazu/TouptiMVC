<?php

class MyRequestMapper extends RequestMapper
{
    protected $_apache;
    protected $_headers = array();

    public function __construct($apache, $headers = array())
    {
        $this->_apache = $apache;
        $this->_headers = $headers;

        parent::__construct();
    }

    protected function getRequestHeaders()
    {
        if ($this->_apache)
        {
            return $this->_headers;
        }
        return $this->getFastCgiRequestHeaders();
    }
}

class TestRequest extends UnitTestCase
{
    public function setUp()
    {
        $_SERVER = array();
    }

    protected function setServerEnv($method, $original_uri = null, $http_accept = NULL)
    {
        $_SERVER = array('REQUEST_METHOD' => $method,
                         'REQUEST_URI' => $original_uri,
                         'HTTP_ACCEPT' => $http_accept);
    }

    protected function assertHttpMethod($good_method)
    {
        $this->setServerEnv($good_method);
        $request = new RequestMapper();
        $this->assertTrue($request->{'is'. ucfirst(strtolower($good_method))}(),
            'is'.ucfirst(strtolower($good_method)) .'() should be true');
        $methods = $request->getPossibleRequestMethods();
        foreach ($methods as $method)
        {
            if ($method == $good_method)
                continue;
            $this->assertFalse($request->{'is'. ucfirst(strtolower($method))}(),
                'is'.ucfirst(strtolower($good_method)) .' should be false');
        }
        $this->assertEqual($good_method, $request->getRequestMethod());
        $this->assertEqual($good_method, $request->method);
    }

    public function testIsGet()
    {
        $this->assertHttpMethod('GET');
    }

    public function testIsPost()
    {
        $this->assertHttpMethod('POST');
    }

    public function testIsPut()
    {
        $this->assertHttpMethod('PUT');
    }

    public function testIsHead()
    {
        $this->assertHttpMethod('HEAD');
    }

    public function testIsDelete()
    {
        $this->assertHttpMethod('DELETE');
    }

    public function testIsOptions()
    {
        $this->assertHttpMethod('OPTIONS');
    }

    public function testIsTrace()
    {
        $this->assertHttpMethod('TRACE');
    }

    public function testIsConnect()
    {
        $this->assertHttpMethod('CONNECT');
    }

    public function testParseNoAcceptHttpHeader()
    {
        $this->setServerEnv('GET');
        $request = new RequestMapper();
        $this->assertNull($request->accept);
    }

    public function testParseAcceptHttpHeaderBuggy()
    {
        $this->setServerEnv('GET', null, 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
        $request = new RequestMapper();
        $this->assertnotNull($request->accept);
        $this->assertEqual(2, count($request->accept));
        $this->assertEqual('text/html', $request->accept[0]);
    }

    public function testHeaderWithApache()
    {
        $this->setServerEnv('GET');
        $request = new MyRequestMapper(true, array('User-Agent' => 'My User Agent'));
        $this->assertEqual('My User Agent', $request->getHeader('User-Agent'));
    }

    public function testHeaderFastCgi()
    {
        $this->setServerEnv('GET');
        $_SERVER['HTTP_USER_AGENT'] = 'My Funky User Agent';
        $request = new MyRequestMapper(false);
        $this->assertEqual('My Funky User Agent', $request->getHeader('User-Agent'));
    }

    public function testHeaderFastCgiWithAdditionalServer()
    {
        $this->setServerEnv('GET');
        $_SERVER['MY_AGENT'] = 'My Funky User Agent2';
        $request = new MyRequestMapper(false);
        $this->assertNull( $request->getHeader('My-Agent'));
    }

    public function testNotXhr()
    {
        $this->setServerEnv('GET');
        $request = new RequestMapper();
        $this->assertFalse($request->isXHR());
    }

    public function testIsXhrWithApache()
    {
        $this->setServerEnv('GET');
        $request = new MyRequestMapper(true, array('X-Requested-With' => 'XMLHttpRequest'));
        $this->assertTrue($request->isXHR());
    }

    public function testIsXhrWithApacheIE()
    {
        $this->setServerEnv('GET');
        $request = new MyRequestMapper(true, array('x-requested-with' => 'XMLHttpRequest'));
        $this->assertTrue($request->isXHR());
    }

    public function testIsXhrWithFastCgi()
    {
        $this->setServerEnv('GET');
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $request = new MyRequestMapper(false);
        $this->assertTrue($request->isXHR());
    }

    public function testGetOriginalUrl()
    {
        $this->setServerEnv('GET', '/test');
        $request = new RequestMapper();
        $this->assertEqual('/test', $request->original_uri);
    }
}
