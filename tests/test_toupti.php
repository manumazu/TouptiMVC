<?php

class MyController extends Controller
{
    public function isAuthorized($method_name)
    {
        if ($method_name != 'action_403')
            return true;
        return false;
    }

    public function adefault()
    {
        return 'plop';
    }

    public function action_403()
    {
    }
}

class TestToupti extends UnitTestCase
{
    public function setUp()
    {
    }

    public function runToupti($uri, $conf = null)
    {
        if(is_null($conf))
        {
            $conf = array('route' => dirname(__FILE__).'/data/routes.php');
        }
        $_SERVER['REQUEST_URI'] = $uri;
        $this->req = new Request();
        $this->res = new TouptiResponse();
        
        $this->app = new MiddlewareStack();
        
        $this->toupti = new Toupti($conf);
        
        $this->app->add($this->toupti);
        $this->app->run($this->req, $this->res);
    }
    
    public function testWrongConfFile()
    {
        $conf = array('route' => dirname(__FILE__).'/data/wrong_routes.php');
        $this->expectException(new TouptiException("Unable to load route from {$conf['route']}."));
        $this->runToupti('/404', $conf);
    }
    public function testEmptyRoute()
    {
        $this->expectException(new RouteNotFound('/404', 404));
        $this->runToupti('/404', array());
    }

    public function testRouteSuccess()
    {
        $this->runToupti('/');
        $this->assertEqual('plop', $this->res->body);
    }

    public function testRouteWith403()
    {
        $this->expectException(new TouptiException('access_not_allowed', 403));
        $this->runToupti('/403');
    }

    public function testRouteControllerError()
    {
        $this->expectException(new TouptiException('Route error. Controller UnknowController not found for /unknow.', 404));
        $this->runToupti('/unknow');
    }

    public function testRouteActionError()
    {
        $this->expectException(new TouptiException('Route error. Action plop not exist in MyController for /unknow_action.', 404));
        $this->runToupti('/unknow_action');
    }
}
