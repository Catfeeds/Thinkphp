<?php
namespace Admin\Controller;
use Think\Controller;
class ProductController extends Controller {

    private $ProductLogic;
    private $Product;
    private $Category;

    public function __construct(){
        parent::__construct();
        $this->ProductLogic = D('Product','Logic');
        $this->categoryLogic=  D('Category','Logic');
        $this->Product = M('Product');
        $this->Category = M('Category');
    }

	private function checkPriv($priv){
		$adminid = session('adminid');
		if(empty($adminid)) $this->redirect('Adminuser/login',0);
		if(!session('issuper')){
			if(!empty($priv) && !in_array($priv,session('privs'))) $this->error('您没有此权限!.');
		}
		$this->assign('adname', session('name'));
	}

    public function index(){
	    $this->checkPriv('3_1_1');
        $p = getCurPage();
        $res = $this->ProductLogic->getProductList($p);
        $this->data = $res;
        $this->total = $this->ProductLogic->getProductTotal();
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

	//改变积分商品的上架状态
	public function productStatus(){
		$this->checkPriv('3_1_5');
		$id = I('get.id',null,'int');
		$params['isonline'] = I('get.type','','int');
		$status = $this->Product->where('id='.$id)->save($params);
		if($status){
			$this->redirect('Product/index');
		}else{
			$this->error('操作失败',U('Product/index'),5);
		}
	}

	//新增积分产品
    public function addproduct(){
        $this->checkPriv('3_1_2');
        $this->assign('errcode', '0');
        $this->assign('act', 'add');
        //$cates = getSortedCategory($this->categoryLogic->getCategoryList());
	    $cates = $this->categoryLogic->getParentCategoryList('49');
	    $this->assign('title','新增积分商品');
        $this->assign('cate',$cates);

	    if(I('post.act') == 'add'){
		    $params['productname'] = trim(I('post.productname'));
			$params['content'] = trim(I('post.content','','string'));
		    $params['contentdesc'] = I('post.contentdesc');
		    $params['score'] = I('post.score','','int');
		    $params['price'] = I('post.price','','float');
		    $params['totalnums'] = I('post.totalnums','','int');
		    $params['lastnums'] = I('post.lastnums',null,'int');
		    $params['isonline'] = I('post.isonline',null,'int');
		    $params['categoryid'] = I('post.cid','','int');
		    $params['creatime'] = date('Y-m-d H:i:s');

		    if($_FILES['logopath']['size']>0) {
			    $upres = $this->upimgfile();
			    if ($upres['error'] == false) {
					    $params['logopath'] = $upres['result']['logopath']['fullpath'];
			    }
		    }

		    $imgs = I('post.img');
		    if(!empty($imgs)){
			    $params['imagepath'] = json_encode($imgs);
		    }

		    $ret = $this->Product->add($params);
		    if($ret){
			    $this->redirect('Product/index');
		    }else{
				$this->error('新增积分产品失败');
		    }
	    }else{
		    $this->display("Product/productedit");

	    }
    }

	//编辑积分产品
    public function editproduct(){
        $this->checkPriv('3_1_3');
        $this->assign('act', 'edit');
        $this->assign('errcode', '0');
	    $this->assign('title','编辑积分商品');
        //$cates = getSortedCategory($this->categoryLogic->getCategoryList());
	    $cates = $this->categoryLogic->getParentCategoryList('49');
	    $this->assign('cate',$cates);

	    if(I('post.act') == 'edit'){
		    $params['productname'] = trim(I('post.productname'));
		    $params['content'] = trim(I('post.content','','string'));
		    $params['contentdesc'] = I('post.contentdesc');
		    $params['score'] = I('post.score','','int');
		    $params['price'] = I('post.price','','float');
		    $params['totalnums'] = I('post.totalnums','','int');
		    $params['lastnums'] = I('post.lastnums',null,'int');
		    $params['isonline'] = I('post.isonline',null,'int');
		    $params['categoryid'] = I('post.cid','','int');
		    $params['creatime'] = date('Y-m-d H:i:s');

		    if($_FILES['logopath']['size']>0) {
			    $upres = $this->upimgfile();
			    if ($upres['error'] == false) {
				    $params['logopath'] = $upres['result']['logopath']['fullpath'];
			    }
		    }

		    $imgs = I('post.img');
		    if(!empty($imgs)){
			    $params['imagepath'] = json_encode($imgs);
		    }

			$id = I('post.id',null,'int');
		    $ret = $this->Product->where('id='.$id)->save($params);

		    if($ret !== false){
			    $this->redirect('Product/index');
		    }else{
			    $this->error('修改数据失败');
		    }
        } else {
            $id=I('get.id','','int');
            if ($id) {
	            $data = $this->Product->getById($id);
                $this->data = $data;
	            $this->simgs =  json_decode($data['imagepath']);
                $this->display("Product/productedit");
            } else {
                $this->error('无效记录');
            }
        }
    }


    public function delproduct(){
        $this->checkPriv('3_1_4');
        $id=I('get.id','','int');
        if($id){
            $this->Product->where('id='.$id)->delete();
            $this->redirect('Product/index');
        }else{
            $this->error('没有该记录');
        }
    }

    public function userproductmgr(){
        $p = getCurPage();
        $res = $this->ProductLogic->getUserProductList(array(),$p);
        $this->data = $res;
        $this->total = $this->ProductLogic->getUserProductTotal(array());
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

    private function upimgfile(){
        $ret = array();
        $upload =  new \Think\Upload();
        $upload->maxSize       = C('ITEM_IMG_MAXSIZE');;
        $upload->exts          = array('jpg', 'jpeg', 'png', 'gif', 'bmp');
        $upload->rootPath      = C('ITEM_PRODUCT_PATH');
        $upload->subName       = array('date', 'Ym');
        $upfinfo = $upload->upload();
        if(!$upfinfo) {// 上传错误提示错误信息
            $ret['error'] = true;
            $ret['result'] = $upload->getError();
        }else{// 上传成功
            foreach($upfinfo as $k=>&$file){
                $file['fullpath'] = $upload->rootPath.$file['savepath'].$file['savename'];
            }
            $ret['error'] = false;
            $ret['result'] = $upfinfo;
        }
        return $ret;
    }

	public function exchangemgr(){
		$p = getCurPage();
		$data = $this->ProductLogic->exchangeList($p);
		$totalcount =  $this->ProductLogic->exchangeTotal();
		$show = constructAdminPage($totalcount);
		$this->assign('page',$show);
		$this->assign('data',$data);
		$this->assign('total',$totalcount);
		$this->display();
	}

	public function exchangeComplete(){
		$id = I('get.id',null,'int');
		$data = $this->ProductLogic->changeUserProduce($id);
		if($data){
			$this->redirect('Product/exchangemgr');
		}else{
			$this->error('操作失败，请重新操作');
		}
	}

	public function productkeywords(){
		$this->checkPriv('3_1_1');
		$p = getCurPage();
		$keywords =  trim(I('get.keywords',null,'string'));
		$pos = strpos($keywords,'.html');
		$keywords = substr($keywords,0,$pos);
		$res = $this->ProductLogic->productkeywords($p,$keywords);
		$show =  constructAdminPage($res['total']);
		$this->assign('total',$res['total']);
		$this->assign('page',$show);
		$this->assign('data',$res['list']);
		$this->display('Product/index');
	}
}