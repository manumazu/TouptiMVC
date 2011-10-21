<?php

require_once(dirname(__FILE__).'/testapp/controller.php');

class TestTouptiTestCase extends TouptiTestCase
{
    public function touptiConf()
    {
        return array('toupti' => array('route' => dirname(__FILE__) .'/testapp/routes.php'),
                     'view'   => 'Mock',
                     'viewConf' => array()
                     );
    }

    public function testGet()
    {
        $this->get('/', array('param'=> 'plip'));
        $view = $this->getTouptiResponse()->body;
        $this->assertEqual($view->get('test') ,'plop');
        $this->assertEqual($view->get('param') ,'plip');
        $this->assertEqual($this->getUrl(), '/?param=plip');
        $this->assertResponse(200);
        $this->assertResponse(array(200));
    }

    public function testGetWithMoreParam()
    {
        $this->get('/index', array('param'=> 'plip', 'param2' => 'plop'));
        $view = $this->getTouptiResponse()->body;
        $this->assertEqual($view->get('param') ,'plip');
        $this->assertEqual($view->get('param2') ,'plop');
        $this->assertEqual($this->getUrl(), '/index?param=plip&param2=plop');
    }

    public function testPost()
    {
        $this->post('/post', array('email' => 'test@example.net'));
        $view = $this->getTouptiResponse()->body;
        $this->assertEqual($view->get('email') ,'test@example.net');
        $this->assertEqual($this->getUrl(), '/post');
    }

    public function testPostWithGetParams()
    {
        $this->post('/post_with_get_params?foo=bar&chuck=norris', array());
        $view = $this->getTouptiResponse()->body;
        $this->assertEqual($view->get('getparams'), 2);
        $this->assertEqual($this->getUrl(), '/post_with_get_params?foo=bar&chuck=norris');
    }

    public function testPostFile()
    {
        $filepath = dirname(__FILE__).'/data/test.pdf'; 
        $f = fopen($filepath, 'r');
        $this->post('/upload', array('file' => $f));
        $this->assertEqual($_FILES['file']['name'], 'test.pdf');
        $this->assertEqual($_FILES['file']['size'], filesize($filepath));
        $this->assertEqual($_FILES['file']['type'], 'application/pdf');
        $this->assertTrue(file_exists($_FILES['file']['tmp_name']));
        $this->assertEqual($_FILES['file']['error'], UPLOAD_ERR_OK);
        fclose($f);
    }

    public function testPostMultipleFile()
    {
        $filepath = dirname(__FILE__).'/data/test.pdf'; 
        $f = fopen($filepath, 'r');
        $this->post('/upload', array('file' => array($f, $f)));
        $this->assertEqual($_FILES['file']['name'][0], 'test.pdf');
        $this->assertEqual($_FILES['file']['size'][0], filesize($filepath));
        $this->assertEqual($_FILES['file']['type'][0], 'application/pdf');
        $this->assertTrue(file_exists($_FILES['file']['tmp_name'][0]));
        $this->assertEqual($_FILES['file']['error'][0], UPLOAD_ERR_OK);
        $this->assertEqual($_FILES['file']['name'][1], 'test.pdf');
        fclose($f);
    }

    public function testSendHeader()
    {
        $this->get('/500');
        $this->assertResponse(500);
        $this->assertResponse(array(500));
        $this->assertHeader('X-FOO', 'bar');
    }

    public function testGetContext()
    {
        $this->get('/multiple_template');
        $context = $this->getTouptiResponse()->body->getContext();
        $this->assertEqual($context['testapp/test.tpl']['foo'], 'bar');
        $this->assertEqual($context['testapp/chuck.tpl']['param'], '2');
    }

    public function testAssertUnitTestCase()
    {
        $this->get('/multiple_template');
        $this->assertNull(null);
        $this->assertNotNull('df');
    }

    public function testControllerWithoutReturnView()
    {
        $this->get('/without_view');
        $this->assertEqual($this->_browser->getContent(), 'foo');
    }
}
