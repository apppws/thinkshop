<?php
namespace app\admin\controller;

use think\Controller;
	
class IndexController extends CommController
{
    public function index(){

        // return 'dssdfs';
        return  $this->fetch();
    }

    public function welcome(){

    	
        return  $this->fetch();
    }

  
    
    
}
