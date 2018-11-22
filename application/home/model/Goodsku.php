<?php	
namespace app\home\model;
use think\Model;
use app\home\model\GoodAttrKey;
class Goodsku extends \think\Model

{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = 'shop_sku';

    // public function getattr(){
    //     return $this->hasMany('GoodAttrKey','attr_symbol_path');
    // }

}

?>