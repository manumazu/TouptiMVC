<?php

class TestAppController extends Controller
{
    public function isAuthorized()
    {
        return true;
    }

    public function adefault()
    {
        $view = $this->getView();
        $view->assign('test', 'plop');
        $view->assign('param', self::$req->params['param']);
        return $view;
    }

    public function index()
    {
        $view = $this->getView();
        $view->assign('param', self::$req->params['param']);
        $view->assign('param2', self::$req->params['param2']);
        return $view;
    }

    public function post()
    {
        $view = $this->getView();
        $view->assign('email', self::$req->params['email']);
        return $view;
    }

    public function post_with_get_params()
    {
        $view = $this->getView();
        $view->assign('getparams', count(self::$req->params));
        return $view;
    }

    public function upload()
    {
        $view = $this->getView();
        $view->assign('foo', 'bar');
        return $view;
    }

    public function error_500()
    {
        self::$res->set_status(500);
        self::$res->set_header('X-FOO', 'bar');
        $view = $this->getView();
        $view->assign('foo', 'bar');
        return $view;
    }

    public function multiple_template()
    {
        $view = $this->getView('test.tpl');
        $view->assign('foo', 'bar');
        $view2 = $this->getView('chuck.tpl');
        $view2->assign('param', 2);
        $view2->assign('view', $view);
        return $view2;
    }

    public function without_view()
    {
        return 'foo';
    }
}
