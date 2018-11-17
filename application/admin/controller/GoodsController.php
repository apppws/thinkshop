<?php
namespace app\admin\controller;

use think\Controller;
use app\admin\model\GoodsType;
use app\admin\model\Goods;
use app\admin\model\Goods_files;
use think\Request;
use Db;
use \org\Auth;
use org\Upload;
use app\admin\model\GoodAttrKey;
use app\admin\model\GoodAttrVal;
use app\admin\model\Goodsku;
class GoodsController extends CommController
{
	// 显示页面
    public function product_category()
    {
		//返回输出的内容
        return $this->fetch();
    }
	// 添加分类商品 并显示 
    public function product_category_add()
    {
		// 连接这个表
        $m = GoodsType::all();
		// var_dump($m);  exit;
		// concat 拼接 path,id  分组 排序
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
    public function goods_type_add(Request $req)
    {
		// 1 接收信息
        $data = $req->param();
		
		// 	// 2 判断 名字不为空 父类id 不为0
        if ($data['name'] != "" && $data['parent_id'] != 0) {
            $goods_type = GoodsType::where('id', $req->parent_id)->find();
            $data['path'] = $goods_type->path . ',' . $goods_type->id;
			// substr_count 统计这个有几个,
            $data['level'] = substr_count($data['path'], ",") + 1;
			// var_dump($data);
            $goods_type = GoodsType::create($data);
			// 判断返回的id
            if ($goods_type->id) {
                return $this->success("添加成功", "product_category", 2);
            } else {
                return $this->error("添加失败");
            }
			// 3 判断是父类  parent_id  == 0
        } else if ($data['name'] != "" && $data['parent_id'] == 0) {
            $data['path'] = '0';
            $data['level'] = 1;
            $goods_type = GoodsType::create($data);
            if ($goods_type->id) {
                return $this->success("添加成功", "product_category", 2);
            } else {
                return $this->error("添加失败");
            }

        } else {
            return $this->error("添加失败,内容不能为空");
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
    public function product_category_del($id)
    {
        $data = GoodsType::where("parent_id", $id)->find();
        if ($data)
            return "分类下面还子分类,不允许删除";
        $re = GoodsType::destroy($id);
        if ($re)
            return 1;

    }

	// 商品列表
    public function product_list()
    {
        $data = DB::table('shop_goods')->select();
		// dump($data);
		// 把值传到页面上
        $this->assign('data', $data);
        return $this->fetch();
    }

	// 添加商品
    public function product_add()
    {
		// 连接这个表
        $m = GoodsType::all();
		// var_dump($m);  
		// concat 拼接 path,id  分组 排序
        $data = Db::table('shop_goods_type')->field("*,concat(path,',',id) as paths")->order('paths')->select();
        foreach ($data as $k => $v) {
            $data[$k]['name'] = str_repeat("|------", $v['level']) . $v['name'];
        }
		// var_dump($data);
		// 模板赋值
        $this->assign('data', $data);
		//返回这个数据
        return $this->fetch();
    }
	 //添加商品到数据库

    public function product_add_goods()
    {


        $goods = new Goods;
		// $goods->allowField(true)->save($_POST);
        $data['goodsname'] = $_POST['goodsname'];
        //获取分类的id及 parent_id
        $str = explode(",", $_POST['tid']);
		// var_dump($str);  
        $data['tid'] = $str[0];
        $data['tpid'] = $str[1];


        $data['unit'] = $_POST['unit'];
        $data['attributes'] = $_POST['attributes'];
        //拼接图片
        $data['imagepath'] = implode(',', $_POST['imagepath']);
       //接收表中数据 
        $data['number'] = $_POST['number'];
        $data['barcode'] = $_POST['barcode'];
        $data['curprice'] = $_POST['curprice'];
        $data['oriprice'] = $_POST['oriprice'];
        $data['cosprice'] = $_POST['cosprice'];
        $data['inventory'] = $_POST['inventory'];
        $data['restrict'] = $_POST['restrict'];
        $data['already'] = $_POST['already'];
        $data['freight'] = $_POST['freight'];
        $data['status'] = $_POST['status'];
        $data['reorder'] = $_POST['reorder'];
        $data['text'] = $_POST['editorValue'];

        if ($goods->save($data)) {

            return $this->success("添加成功", "product_list", "", 2);
        } else {
            return $this->error("添加失败", "product_list", "", 2);
        }

    }
      //添加商品图片数据库
    public function prduct_add_goods_ajax()
    {
        var_dump($_FILES);
        echo 1;
    }
    

    //编辑商品
    public function product_edit(Request $req)
    {
        $m = new GoodsType;
        $g = new Goods;
        $goods = $g->find($req->id);
        // dump($goods);
        $tid = $req->id;
        $data = $m->field("*,concat(path,',',id) as paths")->order('paths')->select();
        // dump($data);
        foreach ($data as $k => $v) {
            $data[$k]['name'] = str_repeat("|------", $v['level']) . $v['name'];
        }

        $images = explode(',', $goods['imagepath']);
        // dump($images);
        $i = new Goods_files;
        $image = [];
        foreach ($images as $v) {
            array_push($image, $i->find($v));
        }
        $this->assign('tid', $tid);
        $this->assign("image", $image);
        $this->assign('data', $data);//分类数据
        $this->assign('goods', $goods);//商品数据

        return $this->fetch();
    }

    public function product_edit_save(Request $req)
    {
        $m = new Goods;
        $data = $m->find($req->id);
            // dump($data); exit;
        $data['goodsname'] = $_POST['goodsname'];
            //获取分类的id及pid

        $str = explode(",", $_POST['tid']);
			// var_dump($str);
        $data['tid'] = $str[0];
        $data['tpid'] = $str[1];


        $data['unit'] = $_POST['unit'];
        $data['attributes'] = $_POST['attributes'];
        $data['imagepath'] = "";
        $data['number'] = $_POST['number'];
        $data['barcode'] = $_POST['barcode'];
        $data['curprice'] = $_POST['curprice'];
        $data['oriprice'] = $_POST['oriprice'];
        $data['cosprice'] = $_POST['cosprice'];
        $data['inventory'] = $_POST['inventory'];
        $data['restrict'] = $_POST['restrict'];
        $data['already'] = $_POST['already'];
        $data['freight'] = $_POST['freight'];
        $data['status'] = $_POST['status'];
        $data['reorder'] = $_POST['reorder'];
        $data['text'] = $_POST['editorValue'];

        if ($data->save($data)) {

            return $this->success("修改成功", "product_list", "", 2);
        } else {
            return $this->error("修改失败", "product_list", "", 2);
        }


    }


    //删除商品
    public function product_edit_delete(Request $req)
    {
        $m = Goods::get($req->id);
        $result = $m->delete();
        if ($result) {
            return $this->success("删除成功", "product_list", "", 2);
        } else {
            return $this->error("删除失败", "product_list", "", 2);
        }
    }


    //商品图片上传
    public function product_add_images()
    {


        $upload = new \org\Upload();// 实例化上传类    
        $upload->maxSize = 3145728;// 设置附件上传大小    
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
        $upload->rootPath = './static/files/'; // 设置附件上传目录    // 上传文件
        $upload->saveName = time() . rand(1111, 9999);
        $date = date("Y-m-d", time());//已上传日期为子目录名
        $upload->saveExt = "png";//上传的文件后缀
        $info = $upload->upload();
        if (!$info) {// 上传错误提示错误信息  

            $this->error($upload->getError());

        } else {// 上传成功 

            $m = new Goods_files;
            $data['filepath'] = '/static/files/' . $date . "/" . $upload->saveName . "." . $upload->saveExt;
            $result = $m->save($data);
            $file = ['id' => $result, 'imagepath' => $data['filepath']];
            echo json_encode($file);

        }
    }

    //商品图片删除
    public function product_del_images()
    {
        $m = new Goods_files;
        $result = $m->delete($_GET['id']);
        if ($result) {
            echo 1;
        } else {
            echo 0;
        }
    }

    /**
     * 商品属性管理
     */
    // 添加属性列表显示
    public function attr(Request $req)
    {
        $goods_id = $req->id;
        // var_dump($goods_id);
        // 把商品id传到 属性表中
        $this->assign('goods_id',$goods_id);
        return $this->fetch();
    }
    // 修改属性
    public function attr_exit()
    {
        $item_id = 1;
        $data = GoodAttrKey::where(['item_id' => $item_id])->with(['itemattrval'])->select();
        $need = [];
        foreach ($data as $item) {
            $need[] = $item->toArray();
        }

        $sku = Goodsku::where(['item_id' => $item_id])->select();
        $skus = [];
        foreach ($sku as $item) {
            $skus[] = $item->toarray();
        }

        $this->view->assign('itemAttr', $need);
        $this->view->assign('Goodsku', json_encode($skus, 320));
        return $this->view->fetch();
    }
    // 添加sku
    public function save_sku(Request $req)
    {
        if (request()->isPost()) {
            $data = request()->post();
            // dump($data);
            $bool = Goodsku::where(['item_id' => $data[0]['item_id']])->delete();
            // console($bool);
            // 循环商品 添加库存量和 价格
            foreach ($data as $item) {
                $sku = new Goodsku();
                $sku->item_id = $req->id;
                $sku->original_price = $item['original_price'];
                $sku->price = $item['price'];
                $sku->stock = $item['stock'];
                $sku->attr_symbol_path = $item['symbol'];
                 $sku->save();   
            }

        }

    }

    // 添加属性
    public function save_attr(Request $req)
    {
        if (request()->isPost()) {
            // 接收所有参数
            $data = request()->post();
            // var_dump($data);
            $key = json_decode($data['key'], true);
            $value = json_decode($data['value'], true);    
            $item_id = $req->id;
            $key_id = [];
            GoodAttrKey::where(['item_id' => $item_id])->delete();
            foreach ($key as $k) {
                $attr_key = GoodAttrKey::where(['attr_name' => $k, 'item_id' => $item_id])->find();
                // dump($attr_key);
                if (!$attr_key) {
                    $attr_key = new GoodAttrKey();
                    $attr_key->attr_name = $k;
                    $attr_key->item_id = $item_id;
                    $attr_key->save();
                }
                $key_id[] = $attr_key->attr_key_id;
            }
            $tm_v = [];

            GoodAttrVal::where(['item_id' => $item_id])->delete();
            foreach ($value as $key => $v) {
                $attr_key_id = $key_id[$key];
                foreach ($v as $v1) {
                    $attr_value = GoodAttrVal::where(['attr_value' => $v1, 'attr_key_id' => $attr_key_id])->find();
                    // dump($attr_value);
                    if (!$attr_value) {
                        $attr_value = new GoodAttrVal();
                        $attr_value->attr_key_id = $attr_key_id;
                        $attr_value->attr_value = $v1;
                        $attr_value->item_id = $item_id;
                        $attr_value->save();
                    }
                    $tm_v[] = $attr_value->symbol;

                }
            }
            // dump($key_id);
            // dump($tm_v);
            return json(['key' => $key_id, 'value' => $tm_v]);

        }

        $this->success('请求成功');
    }


}
