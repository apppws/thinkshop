<?php	
namespace app\index\model;
use think\Model;
class Goodsku extends \think\Model

{
    protected $pk = 'sku_id';
    // 设置当前模型对应的完整数据表名称
    protected $table = 'shop_sku';

}

?>