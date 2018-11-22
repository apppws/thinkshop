<?php

namespace app\home\controller;

use think\Controller;
use think\Request;
use app\home\model\AdminUser;
use Illuminate\Support\Facades\Hash;
use think\facade\Session;
class LoginController extends Controller
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

    /**
     * 处理登录数据
     *
     * @return \think\Response
     */
    public function dologin(Request $req)
    {
        $user = AdminUser::where('admin_name',$req->username)->find();
        var_dump($user);
        if($user){
                Session::set('userid',$user->id);
                Session::set('username',$user->admin_name);
           
            return $this->success('登录成功','/',2);
        }else{
            return $this->error('添加失败','login',2);
        }

    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
