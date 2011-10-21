<?php
/**
 * 
 * Middlware for views rendering and errors manager
 * @author Emmanuel Mazurier, Gilles Robit
 *
 */
class Rendering extends Middleware 
{
	public function run($req, $res) 
	{
		$res->views = array();
		$this->follow($req, $res);
		$this->layout_render($req, $res);
	}
	
	public function layout_render($req, $res)
	{
		$status = $res->get_status();
		
		//apply rendering modes depending of HTTP header status
		switch($status) {
			case '404' : 
				$is_body = false;
				break;
			default :
				$is_body = true;
				break;
		}
		
		if($is_body)
		{//need the body render
			if($req->isXHR())
			{ //don't need layout
				if(isset($res->views['body'])) 
				{
					if($res->views['body'] instanceof View)
					{
						$body = $res->views['body'];
						$res->body = $body->display();
					}
				}
			}
			else 
			{//set layout for total render
				$layout = new View('layout/logged.tpl', $req->params);
				$layout->assign('body',$res->views['body']->display());
				$res->body = $layout->display();
			}
		}
		else 
		{//send response without body
			$layout = new View('layout/404.tpl');
			$res->body = $layout->display();
		}
	}
}