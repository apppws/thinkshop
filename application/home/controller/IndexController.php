<?php
namespace app\home\controller;

use \think\Controller;
use app\home\model\GoodsType;
use app\home\model\GoodAttrKey;
use Db;
use think\Request;
class IndexController extends Controller
{
    public function index()
    {
        /** 
         *  三级分类
         * */
        // 1 连接这个模型
        $m = new GoodsType;
        // 2 取出一级分类
        $type = $m->where('parent_id=0')->select()->toArray();
        // var_dump($type);
        // 3 取出二级分类
        $type2 = array();
        $type3 = array();
        // 循环所有
        foreach ($type as $key => $value) {
            // 定义
            $type[$key]['child'] = array();//二级分类的名字
            // var_dump($type[$key]['child']);
            $type2 = $m->where("parent_id=" . $value['id'])->select()->toArray();//获取二级分类
            // var_dump($type2);
            // 循环二级
            foreach ($type2 as $k => $v) {
                //合并一级与二级分类
                array_push($type[$key]['child'], $v);
                //三级分类的名字
                $type[$key]['child'][$k]['child2'] = array();
                //获取三级分类
                $type3 = $m->where("parent_id=" . $v['id'])->select()->toArray();
                // 循环三级
                foreach ($type3 as $v2) {
                    //合并一级二级三级分类
                    array_push($type[$key]['child'][$k]['child2'], $v2);
                }
            }

        }
        $this->assign('type', $type);
        return $this->fetch();
    }


    public function lists(Request $req)
    {
        // var_dump($req->id);
        header("Content-type:text/html;charset=utf-8");
        // 连接商品表 取出值
        $data=Db::table('shop_goods')->where("tid=".$req->id.' or tpid='.$req->id)->select();
        //  var_dump($data);
        $array=array();
        // 循环得到的商品
        foreach($data as $k=>$v){
            //图片的名字
            $v['image']=array();
            // 图片id
            $imageId=explode(',',$v['imagepath']);
            // var_dump($imageId);
            // 循环图片id
            foreach($imageId as $vid){
                // 取出数据库 图片表  判断商品表中的 imagepath 的id跟这个表中判断 是否相等
                $img=Db::table('shop_goods_files')->field('filepath')->where('id='.$vid)->find();
                // 合并
                 array_push($v['image'],$img);
            }
            array_push($array,$v);
        }
        
        // 把得到的数组内容放到页面中
        $this->assign('data',$array);

        return $this->fetch();
    }

    // 商品详情页面
    public function details(Request $req)
    {
        // 取出商品id
        $goods_id = $req->id;
        // 查询商品
        $goods = DB::table('shop_goods')->where('id',$goods_id)->select();
        // var_dump($goods);
         /** 
         *  三级分类
         * */
        // 1 连接这个模型
        $m = new GoodsType;
        // 2 取出一级分类
        $type = $m->where('parent_id=0')->select()->toArray();
        // var_dump($type);
        // 3 取出二级分类
        $type2 = array();
        $type3 = array();
        // 循环所有
        foreach ($type as $key => $value) {
            // 定义
            $type[$key]['child'] = array();//二级分类的名字
            // var_dump($type[$key]['child']);
            $type2 = $m->where("parent_id=" . $value['id'])->select()->toArray();//获取二级分类
            // var_dump($type2);
            // 循环二级
            foreach ($type2 as $k => $v) {
                //合并一级与二级分类
                array_push($type[$key]['child'], $v);
                //三级分类的名字
                $type[$key]['child'][$k]['child2'] = array();
                //获取三级分类
                $type3 = $m->where("parent_id=" . $v['id'])->select()->toArray();
                // 循环三级
                foreach ($type3 as $v2) {
                    //合并一级二级三级分类
                    array_push($type[$key]['child'][$k]['child2'], $v2);
                }
            }

        }
        // echo '<pre>';
        // var_dump($goods);
        // 取出sku
        $sku = DB::table('shop_sku')->where('item_id',$goods_id)->select();
        // 根据sku_id 取出 val 和 key
        // var_dump($sku[0]['attr_symbol_path']);
        /*
         "SELECT a.attr_value,b.*,c.attr_name FROM shop_attr_val a  
        LEFT JOIN shop_sku b ON a.symbol=b.attr_symbol_path
        LEFT JOIN shop_attr_key c ON a.attr_key_id = c.attr_key_id
        where a.item_id = b.item_id" 
         */
        $keys = GoodAttrKey::where('item_id',$goods_id)->select();
        
        // var_dump($keys);
        $this->assign('type', $type);
        $this->assign('goods',$goods[0]);
        $this->assign('sku',$sku[0]);
        $this->assign('val',$keys);
        return $this->fetch();
    }

    public function sku(Request $req){
        // var_dump($req->id);
        // var_dump($req->attr_val);
        $sku = DB::table('shop_sku')
            ->where('item_id',$req->id)
            ->select();
        var_dump($sku);
        // return json_encode($sku[0]);
    }

}
