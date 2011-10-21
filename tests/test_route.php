<?php

/**
 * @see route.php l136: removing params prefixed with ":", and to be test.
 */
class TestRoute extends UnitTestCase
{
    public function setUp()
    {
        include realpath(dirname( __FILE__ ) . '/testapp/routes.php');
        $this->route = $route;
    }

    protected function assertRouteResult($path, $def, $params, $route_path)
    {
        $result = $this->route->find_route($path);
        $this->assertEqual($def, $result[0]);
        $this->assertEqual($params, $result[1]);
        $this->assertEqual($route_path, $result[2]);
    }

    public function testFindAppRoot()
    {
        $this->assertRouteResult('/', array('controller' => 'TestApp'), array('controller' => 'TestApp'), '');
    }

    public function testAddRouteAndFind()
    {
        $scheme = array('controller' => 'test');
        $this->route->add('test', $scheme);
        $this->assertRouteResult('/test', $scheme, $scheme,  'test');
    }

    public function testAddRouteWithActionAndFind()
    {
        $scheme = array('controller' => 'test', 'action' => 'foo');
        $this->route->add('test', $scheme);
        $this->assertRouteResult('/test', $scheme, $scheme, 'test');
    }

    public function testAddAndFindWithQueryString()
    {
        $scheme = array('controller' => 'test', 'action' => 'foo');
        $this->route->add('test', $scheme);
        $this->assertRouteResult('/test?bar=', $scheme, $scheme, 'test');
    }

    public function testAddRouteAndNoMatch()
    {
        $this->expectException(new RouteNotFound('/test2011', 404));
        $this->route->find_route('/test2011');
    }

    public function testAddRouteWithParam()
    {
        $scheme = array('controller' => 'test', 'action' => 'foo', ':chuck' => '[a-z]+');
        $this->route->add('test/:chuck', $scheme);
        $this->assertRouteResult('/test/norris', $scheme, array('chuck' => 'norris', 'controller' => 'test', 'action' => 'foo'), 'test/:chuck');
    }

    public function testAddRouteWithEmptyParam()
    {
        $scheme = array('controller' => 'test', 'action' => 'foo', ':chuck' => '[a-z]*');
        $this->route->add('test/:chuck', $scheme);
        $this->assertRouteResult('/test/', $scheme, array('chuck' => '', 'controller' => 'test', 'action' => 'foo'), 'test/:chuck');
    }

    public function testRouteWithParamNoMatch()
    {
        $scheme = array('controller' => 'test', 'action' => 'foo', ':chuck' => '[a-z]+');
        $this->route->add('test/:chuck', $scheme);
        $this->expectException(new RouteNotFound('/test/', 404));
        $this->route->find_route('/test/');
    }

    public function testRouteWith2Params()
    {
        $scheme = array(
            'controller' => 'test',
            'action'     => 'foo',
            ':chuck'     => '[a-z]+',
            ':norris'    => '[a-z]+');
        $this->route->add('test/:chuck/:norris', $scheme);
        $this->assertRouteResult('/test/not/possible', $scheme, array('chuck'  => 'not', 'norris' => 'possible', 'controller' => 'test', 'action' => 'foo'), 'test/:chuck/:norris');
    }

    public function testRouteParamNotRestrict()
    {
        $scheme = array(
            'controller' => 'test',
            'action'     => 'foo',
            ':chuck'     => '(.*)',
            ':norris'    => '[a-z]+');
        $this->route->add('test/:chuck/:norris', $scheme);
        $this->assertRouteResult(
            '/test/not/possible',
            $scheme, array('chuck'  => 'not', 'norris' => 'not', 'controller' => 'test', 'action' => 'foo'),
            'test/:chuck/:norris');  // buggy
    }

    public function testAddRouteWithoutScheme()
    {
        $this->route->add('test');
        $this->assertRouteResult('/test', null, array(), 'test');
    }

    public function testRouteWithParamsNoRegexp()
    {
        $scheme = array('controller' => 'test', 'action' => 'foo');
        $this->route->add('test/:chuck/:norris', $scheme);
        $this->assertRouteResult('/test/not/possible', $scheme, array('controller' => 'test', 'action' => 'foo', 'chuck'  => 'not', 'norris' => 'possible'), 'test/:chuck/:norris');
    }

    public function testRouteWithParamsNoRegexpButDeclared()
    {
        $scheme = array('controller' => 'test', 'action' => 'foo', ':chuck', ':norris');
        $this->route->add('test/:chuck/:norris', $scheme);
        $this->assertRouteResult('/test/not/possible', $scheme, array('controller' => 'test', 'action' => 'foo', 'chuck'  => 'not', 'norris' => 'possible'), 'test/:chuck/:norris');
    }
}
