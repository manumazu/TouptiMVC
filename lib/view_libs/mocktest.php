<?php

/**
 * @package Toupti
 */
class MocktestView extends ViewAdaptor
{
    private static $realview;

    protected $view_object = null;

    protected $current_var = array();

    public static function useLib($realview)
    {
        self::$realview = $realview;
    }

    public function __construct($tpl = '', $params = array())
    {
        $this->tpl = $tpl;
        $this->current_var[$tpl] = array();
        $this->view_object = new self::$realview($tpl, $params);
    }

    public static function conf($conf)
    {
        call_user_func(array(self::$realview, 'conf'), $conf);
    }

    public function getContext()
    {
        return $this->current_var;
    }

    public function assign($key, $value)
    {
        if ($value instanceOf View)
        {
            $this->current_var = array_merge($this->current_var, $value->getContext());
        }
        else
        {
            $this->current_var[$this->tpl][$key] = $value;
        }
        return $this->view_object->assign($key, $value);
    }

    public function get($key = null)
    {
         return $this->view_object->get($key);
    }

    public function display($tpl = null)
    {
         return $this->view_object->display($tpl);
    }

    public function fetch($tpl = null)
    {
        return $this->view_object->fetch($tpl);
    }

    public function compile($tpl = null)
    {
        // @fixme ouhou, may be wrong code ...
        return $this->view_object->compile();
    }

}
