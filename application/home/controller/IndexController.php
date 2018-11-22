<?php
namespace app\home\controller;

use \think\Controller;
use app\home\model\GoodsType;
use app\home\model\GoodAttrKey;
use app\home\model\Goodsku;
use app\home\model\Cart;
use app\home\model\Order;
use app\home\model\OrderGoods;
use app\home\model\Address;
use Db;
use think\Request;
use think\Cookie;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;


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
        $data = Db::table('shop_goods')->where("tid=" . $req->id . ' or tpid=' . $req->id)->select();
        //  var_dump($data);
        $array = array();
        // 循环得到的商品
        foreach ($data as $k => $v) {
            //图片的名字
            $v['image'] = array();
            // 图片id
            $imageId = explode(',', $v['imagepath']);
            // var_dump($imageId);
            // 循环图片id
            foreach ($imageId as $vid) {
                // 取出数据库 图片表  判断商品表中的 imagepath 的id跟这个表中判断 是否相等
                $img = Db::table('shop_goods_files')->field('filepath')->where('id=' . $vid)->find();
                // 合并
                array_push($v['image'], $img);
            }
            array_push($array, $v);
        }
        
        // 把得到的数组内容放到页面中
        $this->assign('data', $array);

        return $this->fetch();
    }

    // 商品详情页面
    public function details(Request $req)
    {
        // 取出商品id
        $goods_id = $req->id;
        // 查询商品
        $goods = DB::table('shop_goods')->where('id', $goods_id)->select();
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
        $sku = DB::table('shop_sku')->where('goods_id', $goods_id)->select();
        // 根据sku_id 取出 val 和 key
        // var_dump($sku[0]['attr_symbol_path']);
        /*
         "SELECT a.attr_value,b.*,c.attr_name FROM shop_attr_val a  
        LEFT JOIN shop_sku b ON a.symbol=b.attr_symbol_path
        LEFT JOIN shop_attr_key c ON a.attr_key_id = c.attr_key_id
        where a.item_id = b.item_id" 
         */
        $keys = GoodAttrKey::where('goods_id', $goods_id)->select();
        
        // var_dump($keys);
        $this->assign('type', $type);
        $this->assign('goods', $goods[0]);
        $this->assign('sku', $sku[0]);
        $this->assign('val', $keys);
        return $this->fetch();
    }

    public function sku(Request $req)
    {
        header('Content-Type:text/html; charset=utf-8');
        $attr = $req->attr_id;
        // var_dump($attr);
        $keyid = implode(',', $attr);
        $sku = DB::query(
            "SELECT * from shop_sku WHERE id = 
                    (SELECT sku_id
                        FROM sku_attr a 
                        where attr_val_id in ($keyid)
                        GROUP BY sku_id
                        having count(*) = 2)"
        );
        // var_dump($sku);
        return json_encode($sku[0]);
    }

    //加入购物车
    public function addtocart(Request $req)
    {
        $cartsku = Cart::select();
        // var_dump($req->param());
        $skuid = $req->id;
        $gocount = $req->gc;
        $casku = [];
        $cascount = [];
        foreach ($cartsku as $v) {
            $casku[] = $v->sku_id;
            $cascount[] = $v->goods_count;
        }
        // var_dump(session('userid'));
        // 判断是否登录
        if ($user_id = session('userid')) {
            $data['sku_id'] = $skuid;
            $data['goods_count'] = $gocount;
            $data['user_id'] = $user_id;
                // dd($skuid,$gocount,$user_id);
                // 保存
            $succ = Cart::create($data);
            if ($succ) {
                return $this->success('添加到购物车成功', 'cart', 2);
            } else {
                return $this->error('添加到购物车失败', 'details', 2);
            }
        } else {
            // 如果没有登录
            // 1 数据暂时放到cookie中
            $cart = Cookie::get('cart');
            $cart[$skuid] = $gocount;
            // dd($gocount);
    		// 保存一个月
            Cookie::queue('cart', serialize($cart), 43200);
        }
    }

    // 显示购物车页面
    public function cart()
    {
        $cart = new Cart;
        $cartData = [];
        // 判断是否登录 取出数据
        if ($user_id = session('userid')) {
            // 登陆了就返回 数据
            $db = DB::query("SELECT * FROM shop_cart a 
                            LEFT JOIN shop_sku b ON a.sku_id = b.id 
                            LEFT JOIN shop_goods c ON b.goods_id = c.id
                            WHERE user_id = $user_id");
            // var_dump($db);
            $cartData = $db;
        } else {
            // 如果没有登录
            // 1 数据暂时放到cookie中
            $cart = Cookie::get('cart');
            // dd($cart);
            // 2 判断 cookie 是否有cart这个数据
            if ($cart) {
                // 取出反序列化
                $cart = unserialize($cart);
                $skuids = array_keys($cart); //返回数组所有键名的一个新数组
                // dd($skuids);
                $gsData = DB::query("SELECT * FROM shop_cart a 
                        LEFT JOIN shop_sku b ON a.sku_id = b.id 
                        LEFT JOIN shop_goods c ON b.goods_id = c.id
                        WHERE user_id = $user_id");
                // dd($gsData);
                $gsData->goods_count = $cart;
                // dd($gsData);
                $cartData = $gsData;
            } else {
                $cartData = [];
            }

        }
        $this->assign('cartData', $cartData);
        return $this->fetch();
    }


    // 结算
    public function checkout(Request $req)
    {
        // 判断是否登录
        if ($user_id = session('userid')) {
            // 取出购物车的数据
            $cart = new Cart;
            // 登陆了就返回 数据
            $cartData = DB::query("SELECT * FROM shop_cart a 
                            LEFT JOIN shop_sku b ON a.sku_id = b.id 
                            LEFT JOIN shop_goods c ON b.goods_id = c.id
                            WHERE user_id = $user_id");
           
           
            // dump($cartData);

            $this->assign('cartData', $cartData);
            $this->assign('skuids', $req->sku_id);
            return $this->fetch();
        } else {
            // 如果没登录 就跳转到登录页面
            return redirect()->route('belogin');
        }
    }

    // 生成订单
    public function order_pay(Request $req)
    {
        $order = new Order;
        $paymethods = $req->pay_method;
        // 判断是否登录
        $id = session('userid');
        if (!$id) {
            return redirect('/');
        }
        // 收货人信息
        $address = Address::where('user_id', $id)->select();
        // dump($address);die;
        if (!$address) {
            return redirect('/home/index/cart');
        }
        // 取出购物车的数据
        $cart = new Cart;
        $cartData = DB::query("SELECT * FROM shop_cart a 
                        LEFT JOIN shop_sku b ON a.sku_id = b.id 
                        LEFT JOIN shop_goods c ON b.goods_id = c.id
                        WHERE user_id = $id");
        // dump($cartData);die;
        if (!$cartData) {
            return redirect('/home/index/cart');
        }
        $skuids = $req->skuids;
        // dump($skuids); die;
        // 总价
        $countPirce = 0;
        // 是否勾选了商品
        $xuangoods = false;

        // 循环检测购物车中商品信息
        foreach ($cartData as $v) {
            
            // 检查库存量
            if ($v['goods_count'] > $v['stock'])
                return;
            // 勾选了商品
            $xuangoods = true;
            // 邮费
            $youfei = 0;
            // 累加总价（有规格价格使用规格价格，否则使用本店价）
            $countPirce += $v['goods_count'] * ($v['price'] > 0 ? $v['price'] : $v['cosprice']) + $youfei;
            // dump($countPirce);
        }

        // 如果没有勾选商品
        if (!$xuangoods)
            return redirect('/home/index/cart');


        /**
         * 下订单
         */
       // 开启事务
        Db::startTrans();

       // 订单号
        $orID = $this->StrOrderOne();
    //    dump($orID); die;
        $orderId = Order::insertGetId([
            'orders_id' => $orID,
            'user_id' => $id,
            'name' => $address[0]->name,
            'phone' => $address[0]->phone,
            'province' => $address[0]->province,
            'city' => $address[0]->city,
            'area' => $address[0]->area,
            'address' => $address[0]->address,
            'pay_method' => $paymethods,
        ]);
        // var_dump($orderId);
        if ($orderId) {
            // 购物车中ID
            $cart_sku_id = [];
            // 循环保存购物车中商品
            foreach ($cartData as $v) {
                
                // 添加订单商品
                $dindan = OrderGoods::insert([
                    'order_id' => $orderId,
                    'goods_price' => $v['price'] > 0 ? $v['price'] : $v['cosprice'],
                    'goods_count' => $v['goods_count'],
                    'goods_id' => $v['goods_id'],
                    'goods_name' => $v['goodsname'],
                    'sku_id' => $v['sku_id'],
                ]);
                // 如果出错就回滚事务
                if (!$dindan) {
                    Db::rollback();
                    return redirect('/home/index/cart');
                }

                $cartsku[] = $v['sku_id'];
                // 减库存量
                $dindan = Goodsku::where('id', $v['sku_id'])->find();
                // dump($dindan);die;
                $dindan->stock = Db::raw('stock-' . $v['goods_count']);
                $dindan->save();
                // $dindan = Goodsku::where('sku_id', $v['sku_id']);
                
                // 如果出错就回滚事务
                if (!$dindan) {
                    Db::rollback();
                    return redirect()->route('cart');
                }
            }
            // 如果都成功就提交整个事务
            Db::commit();
            // 从购物车中删除已下单商品
            Cart::whereIn('sku_id', $cartsku)->delete();
        } else {
            Db::rollback();
            return redirect()->route('cart');
        }
        // dd($req->get('pay_method') );
        // 跳转去支付
        if ($paymethods == '支付宝') {
            return redirect('/home/index/pay/orID/'.$orID.'/countPirce/'.$countPirce);
        } elseif ($paymethods == '微信') {
            return redirect('/payByWechat/' . $orID . '/' . $countPirce);
        }

    }


    //  生成唯一的订单号
    public function StrOrderOne()
    {
        /* 选择一个随机的方案 */
        mt_srand((double)microtime() * 1000000);

        return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
    
    public $config =  [
        'app_id' =>'2016091600527335',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqezjjFgYiMrbCWugQqthGzZSk/8JsIJZlLEm6TAfg2CLFQ4Ks3czKaJ7O1Oe6X4X7vVA18KbOKVfI+a0MypWNRcHpqVhbO4jrdawhXQJAgTDgkRZh2UAPZw4aELz1fQxCIto7itY0vQfU94P5tljLX755eFPIbzWfkVHD1pKLlg/Ylrbv0gHXRRuSPDGDznO/hmhsyO+2XqZokqqLDT0lPVDkdHWEGgF+tWRZAza9CEif4/Hs2Gj2z8DO1FF6ieEikC0ywuQDCvSAxUy7cl5AnpzbqzFsSIZgU0+ag+VtfYbV2KQIgJawHziOT9WLJYhSNeHRQBa6jL7e/3VZyyATQIDAQAB',
        'private_key' => 'MIIEpAIBAAKCAQEAxG42Ea9YCkCMyRCB/ZJE9eE4VjI0iAmPB9JV6h1Za5drR9nrBrkYW/9mQd8LxeUzFyVCapWDUe88x6UUYSPaatcsmr6IW8xzrgcWJWszfRaGr3MzMLsQHnV02zyviqNkIlgsgoRApkLWEPwNRlwXGxyg4HtdQIjHeNL4VvOBBNKitI0cO9GNlGXwO8EkA82HZh2TIP6d9emxIzBHs6wJ6CbgmJ2dkAVGeBl7PXtS4/kcg+6fYm/0Xtbq8LL9jOhvR67QhGkXDRU0WU5gAx3Y+xoLs0eR1XXwtHWl45L1vxWGrNI1m+p5ng6Q2d0SRiKrMBNRM6VlqWFICW8HGROUBwIDAQABAoIBAQCrEJgB2sQ/WWvCBqBOJj3sK9GWL27UMg7f7utnUVv4eQuyrTMizbtLEycIoqhsFWji9U3b6I1Oo5w4+ai/2Ct09CMiOGAkIe90VTOSLsPOYfm1fgvMmnw1KnE0JKtzf0vLJSLOH0L2CCrI69jbt4Nf1xS7qnPRqcydio0/nBx2BzuLarZmnePKJ0P4UmsYgQG8J/JLOuULyLNXmwaIodaunj563/bkdHNiBBg2tC/nTb44AR8iY/RAxJW+hnmMnpWvNGpbumI1RaZPepzyrTmHGreB0x656V7b/doub3KSCtPZjwusxVPftS6muzG4tUQ0YM2ftL97iJQvQfrfb4WhAoGBAOQcTO/HYqN68X1iBkykYcJVswOiZhHAVnCnoMjqgt7nJVtnPbvvAl1Igv8BXEND8oSZ7xCV2LXy1xbozWQkLQ/RRLWt8mNZqFbnKFtJ+eUp7dpuyOqW12dmQM2njmmnM4FVZN3MYeobKHDYJlDo07XASmQ0oHbct5NO4DHJ+ELfAoGBANxyWQzvNNqCCnLxKzijm45kCydo712vAVYsNkn1TfONpWl8adJyzkDBwGekrKBs25uA5jOBZICGR7DsT4a3zxt5RpUXX4WMMASUmetdrFyTpAffsib5r+jLLPMXfYOb9C/u5S2z2FN98Tqx+2XfI3bTAag7jXjgfPX5Y4WGdrvZAoGAFekd/r4hHGDXx1peDoiPl1ISAtxbf4MBCosfZ40XCwAa13/AL0gS6xDm/EWOLivdpJ0AmJA8I6XywRGVgPP0nBtWxTizGpXnFInZl4MwjLGNVjjj9ZyNjjIFMXvRsxZLXTXtnVxfX1RCeyxX6dejVkblHmDrtN8Yhv7BjCbBQPMCgYEAvVGqrpQERR/nD12U69h+MGQ0vAy/fSpdsH7ZxNxZrK/Z/eSuEOEtxqleruPaqQ+z7jFeAZ+/Cy3HBed8SMs0n3igqEvhahTB7D0ejubsrrjQ5z4yhoxqiTdsC/0BevSFWmEFCyHnx5RihjDyIUPn9hUy2CME1Wmdh7U8xiB7eckCgYBmyckGg4B8qnnn9fV66wIsaqypb2tPHnXFBbOEfC4u3xHNLU8QCUZc6qoK3wyuVVWs+75yztpZzFE/TYrL9CXi4MjrY38nHFLwvsIXOFTxEnvXbog2T8ECT2Gj1xlOX6Z0vjTV9rLCmw9uq0nYiQ/dSJ4GjaUqtdYPKtdnpz62Qw==',
        'notify_url' => 'http://yansongda.cn/alipayNotify',
        'return_url' => 'http://127.0.0.1:8000/home/index/return',
        // 沙箱模式（可选）
        'mode' => 'dev',
    ];


    // 显示支付页面(支付宝)   // jwelpt9815@sandbox.com
    public function pay(Request $req)
    {
        // var_dump($orID);
        // var_dump($countprice);
       
        // 调用支付宝的网页支付
        $order = [
            'out_trade_no' =>$req->orID, // 订单编号，需保证在商户端不重复
            'total_amount' =>$req->countPirce, // 订单金额，单位元，支持小数点后两位
            'subject' => '支付 pws 商城 的订单：'.$req->orID, // 订单标题
        ]; 
        $alipay = Pay::alipay($this->config)->web($order);

        return $alipay->send();// laravel 框架中请直接 `return $alipay
    }

    // 前端回调页面
    public function return()
    {
        // 校验提交的参数是否合法
        $data = Pay::alipay($this->config)->verify();
        // dd($data);
        echo '<h1>支付成功！</h1> <hr>';
        var_dump( $data->all() );
    }

    // 服务器端回调
    public function notify()
    {
        $data = Pay::alipay($this->config)->verify();
        \Log::debug('Alipay notify', $data->all());
    }

}
