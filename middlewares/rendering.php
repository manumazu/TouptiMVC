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
	
	
	/**
	 * 
	 * Assign values sent to layout if needed
	 * Check status header to display specific error layout
	 * @param $req
	 * @param $res
	 */
	public function layout_render($req, $res)
	{
		$status = $res->get_status();
		
		//apply rendering modes depending of HTTP header status
		switch($status) {
			case '404' : 
				$has_body = false;
				break;
			default :
				$has_body = true;
				break;
		}
		
		if($has_body)
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
				$template = isset($req->params['layout']) ? $req->params['layout'] : 'logged.tpl';
				$layout = new View('layout/'.$template, $req->params);
				//assign values to layout
				if(isset($req->params['_layout_data']))
				{
					$layout->assign_layout_data($req->params['_layout_data']);
				}
				//assign menu to layout
				if(isset($req->params['_layout_menu']))
				{
					$layout->assign_layout_data($req->params['_layout_menu']);
				}				
				//assign js files to layout
				if(isset($req->params['_layout_js']))
				{
					$layout->assign_layout_data($req->params['_layout_js']);
				}
				//assign js files to layout
				if(isset($req->params['_layout_css']))
				{
					$layout->assign_layout_data($req->params['_layout_css']);
				}
				//assign container render
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