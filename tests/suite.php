<?php
$root_path = dirname(__FILE__) . '/..';
require_once('simpletest/autorun.php');
require_once('simpletest/web_tester.php');

require_once($root_path . '/testcase.php');
require_once($root_path . '/request.php');
require_once($root_path . '/response.php');
require_once($root_path . '/route.php');
require_once($root_path . '/middleware.php');
require_once($root_path . '/controller.php');
require_once($root_path . '/toupti.php');
require_once($root_path . '/view.php');
require_once($root_path . '/view_libs/adaptor.php');
require_once($root_path . '/view_libs/smarty.php');
require_once($root_path . '/view_libs/mocktest.php');

class TouptiTestSuite extends TestSuite
{
    public function __construct()
    {
        parent::__construct('Toupti');
        $test_dir = dirname(__FILE__);
        $this->addFile($test_dir .'/test_request.php');
        $this->addFile($test_dir .'/test_response.php');
        $this->addFile($test_dir .'/test_route.php');
        $this->addFile($test_dir .'/test_middleware_stack.php');
        $this->addFile($test_dir .'/test_view.php');
        $this->addFile($test_dir .'/test_toupti.php');
        $this->addFile($test_dir .'/test_touptitestcase.php');
    }
}
