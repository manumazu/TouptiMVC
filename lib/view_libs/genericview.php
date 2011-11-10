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

    /**
     * Build var array assigned to template
     * @see lib/view_libs/ViewAdaptor::assign()
     */
    public function assign($key, $value)
    {
		if (is_array($key))
		{
		   foreach ($key as $k => $v)
		   {
		        $this->var[$k] = $v;
		   }
		}
		else
		   $this->var[$key] = $value;
    }
    
    /**
     * 
     * Build var array assigned to layout
     * @param $data
     */
    public function assign_layout_data($data)
    {
        foreach($data as $k => $value) 
        {
            $this->assign($data[$k], $value);
        }
        
    }

    public function get($key = null)
    {
        return  $this->var[$key];
    }
    
    /**
     * Load template in buffer
     * @see lib/view_libs/ViewAdaptor::display()
     */
    public function display($tpl = null)
    {
   		//global $tab_os;
    	//$tab_os = $this->conf;
    	foreach($this->var as $key => $value) 
    	{
    		$$key = $value;
    	}
    	ob_start();
    	require self::$conf['template_dir'] . $this->tpl;
    	$output = ob_get_contents();
		ob_end_clean();
        return $output;
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