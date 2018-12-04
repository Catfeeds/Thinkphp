<?php
namespace Home\Controller;
use Think\Controller;
class EcommerceController extends Controller {

    public function __construct(){
        parent::__construct();
        $this->Ecommerce= D('EcommerceProduct');
    }

    public function index()
    {
        //必抢  isrush
        $data_rush = $this->Ecommerce->getAllList(array('isrush'=>1));
        //蓝领  isbluec
        $data_blue = $this->Ecommerce->getAllList(array('isbluec'=>1));
        //特价  isspecial
        $data_special = $this->Ecommerce->getAllList(array('isspecial'=>1));
        //热销  isselling
        $data_selling = $this->Ecommerce->getAllList(array('isselling'=>1));
        $this->assign('rush',$data_rush);
        $this->assign('blue',$data_blue);
        $this->assign('special',$data_special);
        $this->assign('selling',$data_selling);
        $this->display('index');
    }

    public function category()
    {
        $type = $this->Ecommerce->getProductTypes();
        $data = array();
        foreach($type as $val){
            $data[$val['id']] = $this->Ecommerce->getAllList(array('type'=>$val['id']));
        }
        $this->assign('type',$type);
        $this->assign('data',$data);
        $this->display('category');

    }
}