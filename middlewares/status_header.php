<?php
/**
 * 
 * Middleware just for catching HTTP header errors 
 * and apply appropriate action
 * @see lib/Middleware::run()
 * @author Emmanuel Mazurier
 *
 */
class StatusHeader extends Middleware 
{
	public function run($req, $res) 
	{
		try {
			$this->follow($req, $res);
		} catch (Exception $e) 
		{
			switch($e->getCode()) 
			{
				case '404':
					$res->set_status(404);
			}
		}
	}

}