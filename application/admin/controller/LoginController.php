<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\admin\model\AdminUser;
use Db;
class LoginController extends CommController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function login()
    {
        return $this->fetch();
    }
    // 处理登录
    public function dologin(Request $req)
    {
        $val = $req->param();
        // var_dump($val);
        $m = new AdminUser;
        $data = Db::table('shop_admin_user')
            ->where('admin_name',$val['admin_name'] )
            ->where('admin_password',md5($val['admin_password']))
            ->find();
        // var_dump($data);
        if ($data) {
            session('user', $data);
            return $this->success("登陆成功", '/admin_index', 2);
        } else {
            return $this->error("登陆失败");
        }
    }

    // 退出
    public function lougout()
    {
        session('user', '');
        return $this->success("正在退出", 'login', 2);
    }

    public function check_error()
    {
        return $this->error("没有权限", '/admin/index/welcome', 2);
    }
}
