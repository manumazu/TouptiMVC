<?php
Class DefaultController extends Controller {
		
    function adefault() {
    	$v = $this->getView('default'); //set template wich must be used for this view
    	$this->res->views['body'] = $v;
    } 
}