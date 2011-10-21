#Toupti

Toupti (pronounced toop-tee) is a micro-framework for PHP5;

It's a rework of Toupti from oz, my works neighbor, https://github.com/oz/toupti
Altougth , goals are a beat different now.

##Toupti's goals are :
    
- be organised and readable as php can be. 
- middleware as in "pass through a stack" (think: got a request, wanna response, how to transform ?).
- use only what you need (no extra and hudge helpers class like count(coffee) with $sugar and toss()), but at least there is some sugar.
- be tested and provide facilities to be tested.
- why not processing other thing than html request ?

#How to start with toupti?

I recommend the following directory tree :

    my_new_app/
        public/
            .htaccess
            app.php
        conf/route.php
        lib/
            toupti.php
	    ...

And the following Apache vhost should do the trick :

    <VirtualHost toupti>
        ServerName toupti
        ServerAdmin foobar@example.com

        DocumentRoot /path/to/my_new_app/public
        <Directory /path/to/my_new_app/public>
            Options All
            AllowOverride All
            Order deny,allow
            allow from 127.0.0.1
        </Directory>
    </VirtualHost>

Now, up to you to choose how you wanna do the job. You'll have to peek up and launch your architecture.
A basic one is to use a standard MVC (provided for now), so you're first job is to cut and past the following into app.php :
(Note that's not a running example, but it's quitly what we need to know and to do about a MVC Toupti)

    $req = new Request();                 // request wraper (one of your best friend).
    $res = new TouptiResponse();          // an other good friend.
    
    $app = new MiddlewareStack();         // this one won't bother you anymore.
    
    $route = new Route();
    $route->add('/say/:something', array('controller' => 'say', ':something'));    // 2011 promise: an http verb instead off "add"
    $toupti = new Toupti($route);         // I know you wanna MVC
    
    $app->add($this->toupti);             // as a final dispatcher
    $app->run($this->req, $this->res);    // to compile the result

    $app->send();                         // and to be sent.

    Class SayController extends Controller {
        function something() { return self::$res->params['what']; } }

Obviously, you'll need to initialize some kind of autoloader, log or whatever from your favourite libs. I Can't pretend to do the best choice for you.

Furthermore to test the app :

    // require some simpletest, touptitestcase dependencies and your's app bootstrap.

    class TestSay extends TouptiTestCase {
        public function touptiConf() {
            return array('toupti' => array('route' => 'path/to/routes.php')); }

        public function testSomething() {
            $this->get('/say/whatever');
            $view = $this->getTouptiResponse()->body;
            $this->assertEqual($view->get('something') ,'whatever');
            $this->assertResponse(array(200)); } }

#Ok, but how does it work?

##The basics

At first what we were calling Toupti is now a Class used 
by TouptiRoute middleware who is a kind of url parser and dispatcher.

What is provide here :

- a middleware runner (MiddlewareStack) which will help you 
to ordonnanced the pipe to transform the request to a response, and send.
- a way to easily wrap your own "must do that on every request |& response" without globals or singleton (ok, ok ... there is still some crapy helpers)
- basic middleware in order to quickly build a simple, basic and understandable mvc (mainly view, controller and http handling facilitise)
- no coffee, but may be there is some beer in the fridge.

Toupti works from your existing PHP class, provided it extends the
Toupti class, and then maps all of its public methods to URL paths. 

##Fun with routing?
(may need some update)

* The default setup

    Toupti provides a simple, yet flexible routing engine. It is configured
    through the $routes array, that you can (should) override to provide
    better routes in your application.

    The default $routes array is:

        $routes = array(''        => 'index',
                        ':action' => ':action');

    The first line maps the path "/" to your action "index".
    The second line maps the path "/:whatever" to your action ":whatever".

* Simple named routes

    You can define more complex routes like:

        $routes = array(... ,
                        'say/:what/to/:recipient' => 'dialogue');

    Which will map "/say/something/to/someone" to the dialogue action.
    Furthermore in dialogue you can access the named parameters through
    the params attribute:

        public function dialogue()
        {
            $this->params['what'] == 'something';       // true
            $this->params['recipient'] == 'someone';    // true
        }

    Named parameters must strictly match the alphanumeric regex pattern
    (that is \w for you).


* Routes with splat params

    $routes = array(...,
                    'say/*/to/*' => 'dialogue');

    This will map any URL starting with "say/" followed
    by other text, followed by "to/", followed by some more text.

    Every-thing matched in betweem "say" and "to" is push in the
    $this->params['splat'] array. So :

        For:     /say/rise/to/lord/vader
        You get: $this->params['splat'] == array('rise', 'lord/vader');

        For:     /say/rise/to/
        You get: $this->params['splat'] == array('rise', '');

        For:     /say/what/tooooo
        You get: 404 error

        For:     /say/meh/to
        You get: 404 error


* Named routes with custom regexes

    Named routes are fun, but can be too strict at times. What if you
    want to map a numerical ID to your fetch_answer action?

    Well you'd write:

        $routes = array(...,
                        'fetch_answer/:id' => array('action' => 'fetch_answer',
                                                    ':id' => '\d+')
                        );

    So /fetch_answer/42 will lead you to the fetch_answer() action, but
    /fetch_answer/fail will only lead you to a 404 error...


    While we're at it, you can write things like :

        $routes = array(...,
                        'foo/:bar' => array('action' => 'do_foo',
                                            ':bar' => 'bar|baz|quux')
                        );

    To match either of these paths to the 'do_foo' action:
        - /foo/bar
        - /foo/baz
        - /foo/quux


    And since it's only fair that you can also do strange things, it is
    possible to map the route action option to a named param:

        $routes = array(...,
                        'foo/:bar/:baz' => array('action' => ':bar',
                                                 ':bar'   => 'edit|delete',
                                                 ':baz'   => '\d+')
                        );

    This will map:
        - /foo/edit/42    to the edit   action with params['id'] == 42.
        - /foo/delete/42  to the delete action with params['id'] == 42.



One extremly important thing to remember is that routes are matched from
top to bottom, and that the first that fits is the only one that will
fire.


##Templates

view adaptor is provided, an aready working smarty backend too.

#Todo

- Handling other template engines.
- Handling other web server.
- More friendly tuto.
- Exemple Apps (be funcky, no MVC).
- Code reviews.
- facilities for phpMD, code coverage, doc gen, build system, config, autoloader.
- Middleware repo.
