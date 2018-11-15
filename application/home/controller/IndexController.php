<?php
namespace app\home\controller;

use \think\Controller;
class IndexController  extends Controller
{
    public function index(){


        return $this->fetch();
    }


    public function lists(){


        return $this->fetch();
    }

    public function details(){
    	return $this->fetch();
    }
}
