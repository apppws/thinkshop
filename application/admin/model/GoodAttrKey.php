<?php	
namespace app\admin\model;
use think\Model;
class GoodAttrKey extends \think\Model

{
    protected $pk = 'attr_key_id';
    // 设置当前模型对应的完整数据表名称
    protected $table = 'shop_attr_key';
}

?>