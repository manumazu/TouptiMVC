#Toupti

Toupti (pronounced toop-tee) is a micro-framework for PHP5;

It's a rework of Toupti from oz, my works neighbor, https://github.com/oz/toupti
Altougth , goals are a beat different now.

This version of Toutpi implement a MVC architecture

##Toupti's goals are :
    
- be organised and readable as php can be. 
- middleware as in "pass through a stack" (think: got a request, wanna response, how to transform ?).
- use only what you need (no extra and hudge helpers class like count(coffee) with $sugar and toss()), but at least there is some sugar.
- be tested and provide facilities to be tested.
- why not processing other thing than html request ?

#How to start with toupti MVC ?

I recommend the following directory tree :

    my_new_app/
        public/
            .htaccess
            index.php
        conf/route.php
        modules/
	    [module]Controller.php
        views
            [module]/
               [action].tpl
        lib/
            toupti.php
	    ...

for other explanations see README in lib directory
