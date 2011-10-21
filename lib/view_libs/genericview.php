<?php
class GenericView extends ViewAdaptor
{
	protected static $conf = array(
        'template_dir' => ''
    );
    public $var = array();
    public static $conf_setup = false;
    public $tpl = null;
    public $params = null;
    
    public static function conf($conf)
    {
        self::$conf = $conf;
    }

    public function __construct($tpl, $params)
    {
        $this->tpl = $tpl;
        $this->params = $params;
        $this->assign('params', $params);
    }

    public function assign($key, $value)
    {
        $this->var[$key] = $value;
    }

    public function get($key = null)
    {
        return  $this->var[$key];
    }

    public function display($tpl = null)
    {
    	foreach($this->var as $key => $value) 
    	{
    		$$key = $value;
    	}
    	ob_start();
        require self::$conf['template_dir'] . $this->tpl;
        return ob_get_clean();
    }

    public function fetch($tpl = null)
    {
        return implode(array_keys($this->$var), ',');
    }

    public function compile($tpl =null)
    {
        return true;
    }
}