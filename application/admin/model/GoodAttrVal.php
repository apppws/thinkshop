<?php	
namespace app\admin\model;
use think\Model;
class GoodAttrVal extends \think\Model

{
    protected $pk = 'symbol';
    // 设置当前模型对应的完整数据表名称
    protected $table = 'shop_attr_val';
}

?>