<?php
namespace Admin\Controller;
use Think\Controller;
header("Content-Type:text/html; charset=utf-8");
class EcommerceController extends Controller {
    public function __construct(){
        parent::__construct();
        $this->ecommerceLogic =  D('Ecommerce','Logic');
        $this->Ecommerce=  M('Ecommerce_product');
    }

    private $ecommerceLogic ;
    private $Ecommerce ;

    private function checkPriv($priv){
        $adminid = session('adminid');
        if(empty($adminid)) $this->redirect('Adminuser/login',0);
        if(!session('issuper')){
            if(!empty($priv) && !in_array($priv,session('privs'))) $this->error('您没有此权限!.');
        }
        $this->assign('adname', session('name'));
    }

    //product
    public function productmgr(){
        $this->checkPriv('3_2_1');
        $p = getCurPage();
        $res = $this->ecommerceLogic->getProductList(array(),$p);
        $this->data = $res;
        $this->total = $this->ecommerceLogic->getProductTotal();
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

    public function addproduct(){
        $this->checkPriv('3_2_2');
        $this->assign('act','add');
        $this->assign('errcode','0');

        if(I('post.act')=='add'){
            $newdata = array();
            $newdata['name'] = I('post.name');
            $newdata['type'] = I('post.type');
            $newdata['price'] = I('post.price');
            $newdata['introduction'] = I('post.introduction');
            $newdata['spread_link'] = I('post.spread_link');
            $newdata['isspecial'] = I('post.isspecial');
            $newdata['isrush'] = I('post.isrush');
            $newdata['isbluec'] = I('post.isbluec');
            $newdata['isselling'] = I('post.isselling');
            $newdata['picture'] = '';
            if($_FILES['picture']['size'] > 0){
                $upres = $this->upimgfile();
                if($upres['error'] == false){
                    $newdata['picture'] = $upres['result']['picture']['fullpath'];
                }
            }
            if( I('post.cornerid') ){
                $newdata['cornerid'] = I('post.cornerid');
            }
            $a =  array_keys(array_map('trim',$newdata),'');
            if($a){
                $this->error('带*号必填项目不能为空');
            }
            $ret = $this->Ecommerce->add($newdata);
            $this->redirect('Ecommerce/productmgr');
        }else{
            $types = $this->ecommerceLogic->getProductTypes();
            $corners = $this->ecommerceLogic->getCorners();
            $this->assign('types',$types);
            $this->assign('title','添加新产品');
            $this->assign('corners',$corners);
            $this->display("Ecommerce/productedit");
        }
    }

    public function editproduct(){
        $this->checkPriv('3_2_3');
        $this->assign('act','edit');
        $this->assign('errcode','0');
        if(I('post.act')=='edit'){
            $newdata = array();
            $id = I('post.id','','int');
            $newdata['name'] = I('post.name');
            $newdata['type'] = I('post.type');
            $newdata['price'] = I('post.price');
            $newdata['introduction'] = I('post.introduction');
            $newdata['spread_link'] = I('post.spread_link');
            $newdata['isspecial'] = I('post.isspecial');
            $newdata['isrush'] = I('post.isrush');
            $newdata['isbluec'] = I('post.isbluec');
            $newdata['isselling'] = I('post.isselling');
            if($_FILES['picture']['size'] > 0){
                $upres = $this->upimgfile();
                if($upres['error'] == false){
                    $newdata['picture'] = $upres['result']['picture']['fullpath'];
                }
            }

            //echo I('post.cornerid');die;
            $newdata['cornerid'] = I('post.cornerid');

            $a =  array_keys(array_map('trim',$newdata),'');
            if($a){
                $this->error('带*号必填项目不能为空');
            }
            $ret = $this->Ecommerce->where('id='.$id)->save($newdata);
            $this->redirect('Ecommerce/productmgr');
        }else{
            $id = I('get.id','','int');
            $data = $this->ecommerceLogic->getEcommerceProductById($id);
            $types = $this->ecommerceLogic->getProductTypes();
            $corners = $this->ecommerceLogic->getCorners();
            $this->assign('types',$types);
            $this->assign('title','修改产品信息');
            $this->assign('data',$data);
            $this->assign('corners',$corners);
            $this->display("Ecommerce/productedit");
        }
    }

    public function delproduct(){
        $this->checkPriv('3_2_4');
        $id = I('get.id','','int');
        if($id){
            $this->Ecommerce->where('id='.$id)->delete();
            $this->redirect('Ecommerce/productmgr');
        }else{
            $this->error('该记录不存在');
        }
    }

    private function upimgfile(){
        $ret = array();
        $upload =  new \Think\Upload();
        $upload->maxSize       = C('ITEM_IMG_MAXSIZE');;
        $upload->exts          = array('jpg', 'jpeg', 'png', 'gif', 'bmp');
        $upload->rootPath      = C('ITEM_IMG_PATH');
        $upload->subName       = array('date', 'Ym');
        $upfinfo = $upload->upload();
        if(!$upfinfo) {// 上传错误提示错误信息
            $ret['error'] = true;
            $ret['result'] = $upload->getError();
            //$this->error($upload->getError());
        }else{// 上传成功
            foreach($upfinfo as $k=>&$file){
                $file['fullpath'] = $upload->rootPath.$file['savepath'].$file['savename'];
            }
            $ret['error'] = false;
            $ret['result'] = $upfinfo;
        }
        return $ret;
    }

}