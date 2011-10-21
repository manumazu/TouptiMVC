<?php

$route = new Route();

$route->add('', array('controller' => 'my'));// Default root-route
$route->add('403', array('controller' => 'my',
                         'action'     => 'action_403'));
$route->add('unknow', array('controller' => 'unknow',
                            'action'     => 'plop'));
$route->add('unknow_action', array('controller' => 'my',
                            'action'     => 'plop'));

