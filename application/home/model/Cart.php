<?php	
namespace app\home\model;

use think\Model;
use app\home\model\GoodAttrVal;

class Cart extends \think\Model

{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = 'shop_cart';

    // public function data()
    // {
    //       // 判断是否登录
    //     if ($user_id = session('userid')) {
    //         // 登陆了就返回 数据
    //         $db = DB::table('cart')
    //             ->select('*')
    //             ->leftJoin('goods_stock', 'cart.sku_id', '=', 'goods_stock.sku_id')
    //             ->leftJoin('goods', 'goods_stock.goods_id', '=', 'goods.id')
    //             ->leftJoin('goods_pic', 'goods_pic.goods_id', '=', 'goods.id')
    //             ->where('user_id', $user_id)
    //             ->get();
    //         return $db;
    //     } else {
    //         // 如果没有登录
    //         // 1 数据暂时放到cookie中
    //         $cart = Cookie::get('cart');
    //         // dd($cart);
    //         // 2 判断 cookie 是否有cart这个数据
    //         if ($cart) {
    //             // 取出反序列化
    //             $cart = unserialize($cart);
    //             $skuids = array_keys($cart); //返回数组所有键名的一个新数组
    //             // dd($skuids);
    //             $gsData = DB::table('cart')
    //                 ->select('*')
    //                 ->leftJoin('goods_stock', 'cart.sku_id', '=', 'goods_stock.sku_id')
    //                 ->leftJoin('goods', 'goods_stock.goods_id', '=', 'goods.id')
    //                 ->leftJoin('goods_pic', 'goods_pic.goods_id', '=', 'goods.id')
    //                 ->where('goods_stock.sku_id', $skuids)
    //                 ->get();
    //             // dd($gsData);
    //             $gsData->goods_count = $cart;
    //             // dd($gsData);
    //             return $gsData;
    //         } else
    //             return [];
    //     }
    // }

}

?>