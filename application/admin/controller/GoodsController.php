<?php
namespace app\admin\controller;

use think\Controller;
use app\admin\model\Goods;
use app\admin\model\GoodsType;
use Db;
class GoodsController extends Controller
{


	public function product_category()
	{
		//返回输出的内容
		return $this->fetch();
	}

   
	// 添加分类商品 并显示 
	public function product_category_add()
	{
		// 连接这个表
		$m =GoodsType::all();
		// var_dump($m);  exit;
		// concat 拼接 path,id  分组 排序
		// $data = $m->field("*,concat(path,',',id) as paths")->order('paths')->select();
		$data = Db::table('shop_goods_type')->field("*,concat(path,',',id) as paths")->order('paths')->select();
		// var_dump($data); exit;
		foreach ($data as $k => $v) {
			$data[$k]['name'] = str_repeat("|------", $v['level']) . $v['name'];
		}
		// var_dump($data);
		// 模板赋值
		$this->assign('data', $data);
		//返回这个数据
		return $this->fetch();
	}

    //添加分类信息到数据库
	public function goods_type_add()
	{
			// 1 接收信息
		$data['name'] = $_POST['name'];
		$data['parent_id'] = $_POST['parent_id'];
			//2 连接表
		$m = M('goods_type');
			// 3 判断 名字不为空 父类id 不为0
		if ($data['name'] != " " && $data['parent_id'] != 0) {

			$path = $m->field("path")->find($data['parent_id']);
			$data['path'] = $path['path'];
				// substr_count 统计这个有几个,
			$data['level'] = substr_count($data['path'], ",");
				// var_dump($data['level']); int(3)
			$re = $m->add($data);//返回插入id   
				// var_dump($re); //10
				// 添加时候 修改
			$path['id'] = $re;
			$path['path'] = $data['path'] . ',' . $re;  //拼接 path 路由
			$path['level'] = substr_count($path['path'], ",");
			$res = $m->save($path);
				// var_dump($res);
			if ($res) {

				return $this->success("添加成功", "product_category_add", 2);
			} else {
				return $this->error("添加失败", "product_category_add", 2);
			}
		} else if ($data['name'] != "" && $data['parent_id'] == 0) {
    			
	    		//$path=$m->field("path")->find($data['parent_id']);
			$data['path'] = $data['parent_id'];
			$data['level'] = 1;
			$re = $m->add($data);//返回插入id


			$path['id'] = $re;
			$path['path'] = $data['path'] . ',' . $re;

			$res = $m->save($path);
			if ($res) {

				return $this->success("添加成功", "product_category_add", "", 2);
			} else {
				return $this->error("添加失败", "product_category_add", "", 2);
			}

		} else {
			return $this->error("添加失败,内容不能为空", "product_category", "", 2);

		}


	}
	
	 //获取分类数据
	public function product_category_ajax()
	{
		// $m =GoodsType::all();
		// 条件
		$data = Db::table('shop_goods_type')->field('id,parent_id,name')->select();
		// 结果是json格式
		echo json_encode($data);

	}

		//删除分类信息
	public function product_category_del()
	{
		$id = $_GET['id'];
		$m =new GoodsType;
		$data = $m->where("parent_id=" . $id)->find();

		if ($data) {
			$str = "分类下面还子分类,不允许删除";
			echo json_encode($str);
		} else {
			$re = $m->delete($id);
			if ($re) {
				echo 1;
			}
		}
	}

	// 商品列表
	public function product_list()
	{
		$m = M('goods');
		$data = $m->select();
		// var_dump($data); die();
		// 把值传到页面上
		$this->assign('data', $data);
		return $this->fetch();
	}

	// 添加商品
	public function product_add(){
		// 连接这个表
		$m = M('goods_type');
		// var_dump($m);  
		// concat 拼接 path,id  分组 排序
		$data = $m->field("*,concat(path,',',id) as paths")->order('paths')->select();
		foreach ($data as $k => $v) {
			$data[$k]['name'] = str_repeat("|------", $v['level']) . $v['name'];
		}
		// var_dump($data);
		// 模板赋值
		$this->assign('data', $data);
		//返回这个数据
		return $this->fetch();
	}






}
