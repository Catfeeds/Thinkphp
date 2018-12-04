<?php
namespace Admin\Controller;
use Think\Controller;
header("Content-Type:text/html; charset=utf-8");
class CategoryController extends Controller {
    public function __construct(){
        parent::__construct();
        $this->categoryLogic=  D('Category','Logic');

        $this->Category=  M('Category');
    }

    private $categoryLogic;
    private $Category;

    private function checkPriv($priv){
        $adminid = session('adminid');
        if(empty($adminid)) $this->redirect('Adminuser/login',0);
        if(!session('issuper')){
            if(!empty($priv) && !in_array($priv,session('privs'))) $this->error('您没有此权限!.');
        }
        $this->assign('adname', session('name'));
    }

    public function categorymgr(){
        $this->checkPriv('7_1_0');
        $p = getCurPage();
        $where=array();
        $res = $this->categoryLogic->getCategoryList($where,$p);
        //var_dump($res);die;
        $this->data = getSortedCategory($res);
        $this->total = $this->categoryLogic->getCategoryTotal();
        $show = constructAdminPage($this->total);
//        $this->assign('page',$show);
        $this->display();
    }


    public function addcategory(){
        $this->checkPriv('7_1_3');
        $this->assign('act','add');
        $this->assign('errcode','0');
        if(I('post.act')=='add'){
            $newdata = array();
            $newdata['title'] = I('post.title');
            $newdata['pid'] = I('post.type');
            $newdata['name'] = I('post.name');
            $newdata['keywords'] = I('post.keywords');
            $newdata['description'] = I('post.description');

	        if($_FILES['img']['size']>0 ) {
		        $upres = $this->upimgfile();
		        if ($upres['error'] == false) {
			        $newdata['img'] = $upres['result']['img']['fullpath'];
		        }
	        }

            $ret = $this->Category->add($newdata);
            if($ret){
                $this->redirect('Category/categorymgr');
            }else{
                $this->error('插入数据错误');
            }
        }else{
            $p = getCurPage();
            $cates = getSortedCategory($this->categoryLogic->getCategoryList());
            $this->assign('cate',$cates);
            $this->display("Category/categoryedit");
        }
    }

    public function editcategory(){
        $this->checkPriv('7_1_1');
        $this->assign('act','edit');
        $this->assign('errcode','0');
        if(I('post.act')=='edit'){
            $newdata = array();
            $id = I('post.id','','int');
            $newdata['title'] = I('post.title');

            $newdata['pid'] = I('post.type');
            $newdata['name'] = I('post.name');
            $newdata['keywords'] = I('post.keywords');
            $newdata['description'] = I('post.description');

	        if($_FILES['img']['size']>0 ) {
		        $upres = $this->upimgfile();
		        if ($upres['error'] == false) {
			        $newdata['img'] = $upres['result']['img']['fullpath'];
		        }
	        }

            $ret = $this->Category->where('id='.$id)->save($newdata);
            if($ret){
                $this->redirect('Category/categorymgr');
            }else{
                $this->assign('errcode','1');  // 修改失败
                $this->error('编辑数据错误');
            }
        }else{

            $id = I('get.id','','int');
            $data = $this->categoryLogic->getCategoryById($id);
            $this->data = $data;
            $p = getCurPage();
            $cates = getSortedCategory($this->categoryLogic->getCategoryList(array(),$p));
            $this->assign('cate',$cates);
            $this->display("Category/categoryedit");
        }
    }

    public function delcategory(){
        $this->checkPriv('7_1_2');
        $id = I('get.id','','int');
        if($id){
            $cates = $this->categoryLogic->getChildCateById($id);
            if($cates){
                $this->error('请先删除此类别下面的子类别');
            }
            $data['isdel']= date("Y-m-d H:i:s");;
            $this->Category->where('id='.$id)->save($data);
            $from = I('server.HTTP_REFERER');
            redirect($from);
        }else{
            $this->error('该记录不存在');
        }
    }

	public function getCategory(){
		$id = I('post.id',null,'int');
		$data = $this->categoryLogic->getParentCategoryList($id);
		echo(json_encode($data));
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
			print_r($upfinfo);die;
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

}