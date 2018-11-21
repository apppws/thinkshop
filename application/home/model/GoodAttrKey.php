<?php	
namespace app\home\model;
use think\Model;
use app\home\model\GoodAttrVal;
class GoodAttrKey extends \think\Model

{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = 'shop_attr_key';
    public function vals(){
        return $this->hasMany('GoodAttrVal','attr_key_id');
    }
}

?>