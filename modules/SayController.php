<?php
Class SayController extends Controller {
	
	public function __construct($req, $res)
	{
		parent::__construct($req, $res);
	}
		
    function adefault() 
    {
    	$v = $this->getView('default'); //set template wich must be used for this view
    	$v->assign('hello','hello word');
    	$this->res->views['body'] = $v;
    }

    function something() 
    {
		if($this->req->params['something'] == 'error')
		{//manage dynamic errors with routing params
			$this->res->set_status('404');
		}  
		  	
    	$v = $this->getView('something'); //set template wich must be used for this view
    	$v->assign('what',$this->req->params['something']);
    	
    	$this->res->views['body'] = $v;
    	
    }
}