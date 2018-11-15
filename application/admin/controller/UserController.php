<?php
namespace app\admin\controller;

use think\Controller;
use app\admin\model\Group;
use app\admin\model\Rule;
use think\Request;
use app\admin\model\AdminUser;
use app\admin\model\GroupAccess;
class UserController extends Controller
{
   
    // 显示角色页面
    public function admin_role()
    {
        $data = Group::all();
        // dump($data);
        // 模板赋值
        $this->assign('data', $data);
        return $this->fetch();
    }
    // 添加角色
    public function admin_role_add()
    {
        $data = Rule::all();
        $this->assign('data', $data);
        return $this->fetch();
    }
    // 处理添加角色表单
    public function admin_role_add_user()
    {
        // 接收表单数据
        $data['title'] = $_POST['title'];
        $data['rules'] = implode(",", $_POST['rules']);
        $data['status'] = 1;
        // 调用模型 插入到数据库中
        $m = new Group;
        $result = $m->save($data);
        if ($result) {
            return $this->success("成功", "admin_role", 2);
        } else {
            return $this->error("失败");

        }
    }
    // 删除角色表单
    public function admin_role_delete(Request $req){
        $m = Group::get($req->id);
        $result = $m->delete();
        if ($result) {
            return $this->success("删除成功", "admin_role", "", 2);
        } else {
            return $this->error("删除失败", "admin_role", "", 2);
        }
    }

    // 权限管理列表
    public function admin_permission(){
        // 取出权限
        $data = Rule::all();
        $this->assign('data', $data);
        return $this->fetch();
    }
    // 添加权限管理
    public function admin_permission_add(){
        return $this->fetch();    
    }
    // 处理权限表单
    public function admin_permission_add_data(){
        // 接收数据
        $data['name']=$_POST['name'];
        $data['title']=$_POST['title'];
        $data['type']=1;
        $data['status']=1;
        $m = new Rule;
        $result = $m->save($data);
        if ($result) {
            return $this->success("成功", "admin_permission", 2);
        } else {
            return $this->error("失败");

        }
    }
    // 删除权限
    public function admin_permission_delete(Request $req){
        $m = Rule::get($req->id);
        $result = $m->delete();
        if ($result) {
            return $this->success("删除成功", "admin_permission", "", 2);
        } else {
            return $this->error("删除失败", "admin_permission", "", 2);
        }
    }

    // 管理员列表
    public function admin_list(){
        $data = AdminUser::all();
        $this->assign('data',$data);
        return $this->fetch();
    }

    // 添加管理员
    public function admin_add(){
        // 获取所有角色
        $data = Group::all();
        $this->assign('data',$data);
        return $this->fetch();
    }
    // 处理表单
    public function admin_add_user(){
     
            $data['admin_name']=$_POST['admin_name'];
            $data['admin_password']=md5($_POST['admin_password']);
                
            $m=new AdminUser;//用户数据库
            // 查询数据库 是否名字一样
            $name=$m->where("admin_name='".$data['admin_name']."'")->find();
            // var_dump($name);die;

            //重复名字过滤    
            if(!$name){
                    $res1=$m->save($data);//用户数据库
                    // var_dump($res1);die;
                    $g=new GroupAccess;//分组数据库
                    $group['group_id']=$_POST['group_id'];
                    $group['uid']=$res1;
                    $res2=$g->save($group);//分组数据库


                    if($res1 && $res2){

                        return  $this->success("成功","admin_list",2);
                    }else{

                       return $this->error("失败");

                    }
            }else{
                    return $this->error("当前用户名已存在");
            }
            
       
    }
    // 删除角色
    public function admin_delete(Request $req){
        $m = AdminUser::get($req->id);
        $result = $m->delete();
        if ($result) {
            return $this->success("删除成功", "admin_list", "", 2);
        } else {
            return $this->error("删除失败", "admin_list", "", 2);
        }
    }


}


