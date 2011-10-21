<?php
/**
 * @package Toupti
 */

class SmartyView extends ViewAdaptor
{
    protected static $conf = array(
        'template_dir' => '',
        'compile_dir' => '',
        'cache_dir' => '',
        'config_dir' => '',
        'plugins_dir' => ''
    );

    protected $smarty = null;

    public static function conf($conf)
    {
        self::$conf = $conf;
    }

    public function __construct($tpl = '', $params = array())
    {
        $this->smarty = new Smarty();
        $this->smarty->template_dir = self::$conf['template_dir'];
        $this->smarty->compile_dir = self::$conf['compile_dir'];
        $this->smarty->cache_dir = self::$conf['cache_dir'];
        $this->smarty->config_dir = self::$conf['config_dir'];
        $this->smarty->plugins_dir []= self::$conf['plugins_dir'];

        $this->tpl = $tpl;
        $this->params =  $params;
        $this->assign('params', $this->params);
    }

    public function assign($key, $value)
    {
        if($value instanceof View)
        {
            $this->notify($value->getNotifs());
            $value = $value->fetch();
        }
        $this->smarty->assign($key, $value);
    }

    public function get($key = null)
    {
         return $this->smarty->get_template_vars($key);
    }

    public function display($tpl = null)
    {
        if(!is_null($tpl))
        {
            $this->tpl = $tpl;
        }
        if($this->tpl != "")
            $this->smarty->display($this->tpl);
    }

    public function fetch($tpl = null)
    {
        if(!is_null($tpl))
        {
            $this->tpl = $tpl;
        }
        if($this->tpl == '')
        {
            return;
        }
        return $this->smarty->fetch($this->tpl);
    }

    public function compile($tpl = null)
    {
        if(!is_null($tpl))
        {
            $this->tpl = $tpl;
        }
        if($this->tpl == '')
        {
            return;
        }
        return $this->smarty->createTemplate($this->tpl)->compileTemplateSource();
    }

}
