<?php

$route = new Route();

$route->add('', array('controller' => 'TestApp'));
$route->add('index', array('controller' => 'TestApp', 'action' => 'index'));
$route->add('post', array('controller' => 'TestApp', 'action' => 'post'));
$route->add('post_with_get_params', array('controller' => 'TestApp', 'action' => 'post_with_get_params'));
$route->add('upload', array('controller' => 'TestApp', 'action' => 'upload'));
$route->add('multiple_template', array('controller' => 'TestApp', 'action' => 'multiple_template'));
$route->add('500', array('controller' => 'TestApp', 'action' => 'error_500'));
$route->add('without_view', array('controller' => 'TestApp', 'action' => 'without_view'));
