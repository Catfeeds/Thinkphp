<?php
/**
 * Created by Jessica.
 * User: IceLight
 * Date: 16/5/16
 * Time: 上午9:02
 */

namespace Admin\Logic;


class EcommerceLogic extends \Think\Model{
    public function __construct(){
        $this->ecommerceProduct = M('Ecommerce_product');
        $this->ecommerceProductTypes = M('Ecommerce_product_type');
        $this->ecommerceCorners = M('Corner');
    }
    private $ecommerceProduct;
    private $ecommerceProductTypes;
    private $ecommerceCorners;

    public function getProductTotal($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $num = $this->ecommerceProduct->where($mycond)->count();
        return $num;
    }

    public function getProductList($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $fields = 'pl_ecommerce_product_type.id as typeid,pl_ecommerce_product_type.typename,pl_ecommerce_product.*';
        $data = $this->ecommerceProduct->join('pl_ecommerce_product_type on pl_ecommerce_product_type.id = pl_ecommerce_product.type')->where($mycond)->order('creatime desc')->page($pstr)->field($fields)->select();
        return $data;
    }

    public function getProductTypes(){
        $data = $this->ecommerceProductTypes->select();
        return $data;
    }

    public function getEcommerceProductById($id){
        if($id){
            $data = $this->ecommerceProduct->getById($id);
            return $data;
        }else{
            return false;
        }
    }

    public function getCorners(){
        return $this->ecommerceCorners->where(array('type'=>3,'status'=>1,'isdel'=>array('exp','is null')))->select();
    }

}