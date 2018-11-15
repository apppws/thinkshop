<?php
namespace app\admin\controller;

use think\Controller;
use \org\Auth;
class CommController extends Controller
{ 
    // 当任何函数加载时候  会调用此函数
    public function initialize()
    {//默认的方法  会自动执行 特征有点像构造方法
        $uid = session('user')['id'];
        // var_dump($uid);die;
        if (empty($uid)) {
            echo '<script>alert("没有登陆");location.href="' . '/admin/login/login' . '"</script>';
        }
        //最高管理权限
        if($uid==1){
            // 如果为1 不判断权限
            return true;
        }
        /**
         * 权限判断
         */
    //     $AUTH = new \org\Auth();
            // 找到这个路径  在表格中判断
    //     $name = request()->module().'/'.request()->controller().'/'.request()->action();
    //     // var_dump($name);die;
    //     if (!$AUTH->check($name, session('user')['id'])) {

    //         echo '<script>location.href="' . '/admin/login/check_error' . '"</script>';
    //     }


    }

}
