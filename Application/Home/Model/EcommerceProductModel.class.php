<?php
namespace Home\Model;
use Think\Model;
class EcommerceProductModel extends Model {

    //不分页
    public function getAllList($where=array())
    {
        $whArr = array();
        if(is_array($where) && count($where)>0){
            $whArr = $where;
        }
        $data = $this->alias('p')->join('pl_ecommerce_product_type as t on t.id = p.type')->where($whArr)->select();
        return $data;
    }

    public function getProductTypes(){
        $data = M('ecommerceProductType')->select();
        return $data;
    }
}
?>