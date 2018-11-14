<?php	
namespace app\admin\model;
use think\Model;
class Goods_files extends \think\Model

{
    // 默认主键为id
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = 'shop_goods_files';
}

?>