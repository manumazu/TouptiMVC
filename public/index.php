<?php
require '../lib/toupti.php';
require '../lib/view_libs/adaptor.php';
require '../lib/view_libs/genericview.php';
require '../middlewares/rendering.php';
require '../middlewares/status_header.php';

View::useLib('generic');
View::conf(array('template_dir'=>realpath(dirname(__FILE__)).'/../views/')); //set default rep views (need autoloader)

$req = new Request(); // request wraper (one of your best friend).
$res = new TouptiResponse(); // an other good friend.
$app = new MiddlewareStack(); // this one won't bother you anymore.

$toupti = new Toupti(); // I know you wanna MVC
$toupti->route->add('', array('controller' => 'default'));
$toupti->route->add('say', array('controller' => 'say')); //default route for say module
$toupti->route->add('say/:something', array('controller' => 'say', 'action' => 'something', ':something')); //generic route for say module

//middlewares
$app->add(new StatusHeader()); 
$app->add(new Rendering()); // views render middleware
$app->add($toupti); // as a final dispatcher

$app->run($req, $res); // to compile the result

$res->send(); // and to be sent.
