<?php

/**
 * @package Toupti
 */
class Controller
{
    public $req = null;
    public $res = null;
    static $toupti = null;

    public function __construct($req, $res)
    {
        // params and toupti are always here for migration, they must desapear.
        $this->toupti = self::$toupti;
        $this->setRequest($req);
        $this->setResponse($res);
    }

    public function setResponse($res)
    {
        $this->res = $res; 
    }

    public function setRequest($req)
    {
        $this->req = $req;
    }

    public static function setToupti($toupti)
    {
        self::$toupti = $toupti;
    }

   /**
     * Quick Alias to get a view.
     * @param tpl a full formated path (relative to the app/view one) to the tpl file to use. If tpl is not provide or
     * null, will use view/controller_name/controller_name.tpl. Without any path indication, file will be search in view/controller_name path/.
     * note that you don't need to provide the file extension.
     * @return View
     */
    public function getView($tpl = null)
    {
        // extract controller_name
        $module = strtolower(substr( get_class($this), 0, - strlen("controller")));
        if(is_null($tpl))
        {
            // at least the file is the controller name.
            $tpl = "$module.tpl";
        }
        if(strstr($tpl, '/') === FALSE)
        {
            // not path, so it's our controller template dir'
            $tpl = "$module/$tpl";
        }
        if( strrpos($tpl, ".tpl") != (strlen($tpl) - 4) )
        {
            // missing file extension. note the portnawak test :)
            $tpl .= '.tpl';
        }
        return new View($tpl, $this->req->params);
    }
   
    /**
     * quick alias to exit without any layout.
     * @deprecated
     */
    public function exit_ajax()
    {
        return self::$req->isXHR();
    }

    /**
     * quick alias to exit without any layout.
     * @deprecated
     */
    public function request()
    {
        return self::$req;
    }
    /**
     * Redirect to previous path or exists and outputs in ajax mode
     * @param  string    $ajaxoutput          What to output if we are in ajax mode
     * @param  array     $ajax_headers        Extra headers to output if existing in ajax (probably we will want to put here an x-ajax-referrer)
     * @param  boolean   $redirect_to         if we want to fall back on something in case referrer is not here
     * @deprecated
     * @return void
     */
    public function redirect_to_referrer_or_exit_ajax($ajaxoutput = "", $ajax_headers = array(), $redirect_to = "/")
    {
        if($this->exit_ajax())
        {
            self::$res->set_header($ajax_headers);
            self::$res->body = ($ajaxoutput instanceof View ? $ajaxoutput->fetch(): $ajaxoutput);
        }
        else
        {
            self::$req->redirect(isset($_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : $redirect_to);
        }
    }

    /**
     * Is the called action is authorized ?
     * This can be overloaded in each controller to get a particular acl system
     *
     * @return Boolean
     */
    public function isAuthorized($method_name)
    {
        return true;
    }
}
