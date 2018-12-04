<?php
namespace Admin\Controller;
use Think\Controller;
header("Content-Type:text/html; charset=utf-8");
class PostController extends Controller {
    public function __construct(){
        parent::__construct();
        $this->postLogic=  D('Post','Logic');
        $this->categoryLogic=  D('Category','Logic');
        $this->Post=  M('Post');
        $this->Category=  M('Category');
    }

    private $postLogic;
    private $categoryLogic;
    private $Category;
    private $Post;

    private function checkPriv($priv){
        $adminid = session('adminid');
        if(empty($adminid)) $this->redirect('Adminuser/login',0);
        if(!session('issuper')){
            if(!empty($priv) && !in_array($priv,session('privs'))) $this->error('您没有此权限!.');
        }
        $this->assign('adname', session('name'));
    }
    //comment
    public function postmgr(){
        $this->checkPriv('5_1_0');
        $p = getCurPage();
        $res = $this->postLogic->getPostList(array(),$p);

        $this->total = $this->postLogic->getPostTotal();
        $show = constructAdminPage($this->total);
        $this->data = $res;
        $this->assign('page',$show);
        $this->display();
    }

    public function ajaxcomments(){
        $type=I('post.type','','int')?I('post.type','','int'):1;
        $data = $this->videoLogic->getVideoByType($type);
        $this->ajaxReturn($data);
    }

    public function addpost(){
        $this->checkPriv('5_1_1');
        $this->assign('act','add');
        $this->assign('errcode','0');
        if(I('post.act')=='add'){
            $newdata = array();
            $newdata['title'] = I('post.title');
          //  $newdata['type'] = I('post.type');
            $newdata['cid']= I('post.cate');
            $newdata['content']= I('post.content');
	        $newdata['author'] =  I('post.author');
          //  $newdata['author']= session('username');
            $newdata['creatime'] = date('Y-m-d H:i:s');

	        $newdata['videourl']= I('post.filepath');
	        /*
	        if($_FILES['filepath']['size']>0) {
		        $upfile = $this->upfile();
		        if ($upfile['error'] == false) {
			        $newdata['videourl'] = $upfile['result']['filepath']['fullpath'];
		        }
	        }*/

            $ret = $this->Post->add($newdata);
            if($ret){
                $this->redirect('Post/postmgr');
            }else{
                $this->error('插入数据错误');
            }
        }else{
            //$cates = getSortedCategory($this->categoryLogic->getCategoryList(array('pid'=>52)));
            $cates = $this->categoryLogic->getCategoryList(array('pid'=>52));
            $this->assign('cate',$cates);
            $this->display("Post/postedit");
        }
    }

    public function editpost(){
        $this->checkPriv('5_1_2');
        $this->assign('act','edit');
        $this->assign('errcode','0');
        if(I('post.act')=='edit'){
            $newdata = array();
            $id = I('post.id','','int');
            $newdata['title'] = I('post.title');
           // $newdata['type'] = I('post.type');
            $newdata['cid']= I('post.cate');
            $newdata['content']= htmlspecialchars_decode(trim(I('post.content')));

	        $newdata['author'] = I('post.author');
          //  $newdata['author']= session('username');
            $newdata['modifytime'] = date('Y-m-d H:i:s');

	        $newdata['videourl']= I('post.filepath');
	        /*
	        if($_FILES['filepath']['size']>0) {
		        $upfile = $this->upfile();
		        if ($upfile['error'] == false) {
			        $newdata['videourl'] = $upfile['result']['filepath']['fullpath'];
		        }
	        }*/

            $ret = $this->Post->where('id='.$id)->save($newdata);
            $this->redirect('Post/postmgr');
        }else{
            $id = I('get.id','','int');
            $data = $this->postLogic->getPostById($id);
            $this->data = $data;
           // $cates = getSortedCategory($this->categoryLogic->getCategoryList());
            $cates = $this->categoryLogic->getCategoryList(array('pid'=>52));
	    $this->assign('cate',$cates);
            $this->display("Post/postedit");
        }
    }

    public function delpost(){
        $this->checkPriv('5_1_3');
        $id = I('get.id','','int');
        if($id){
            $data['isdel']= date("Y-m-d H:i:s");
            $this->Post->where('id='.$id)->save($data);
            $from = I('server.HTTP_REFERER');
            redirect($from);
        }else{
            $this->error('该记录不存在');
        }
    }

	private function upfile(){
		$ret = array();
		$upload =  new \Think\Upload();
		$upload->maxSize       = C('ITEM_FILE_SIZE');;
		$upload->exts          = array('zip','jar','apk','doc','xls','ts');
		$upload->rootPath      = C('ITEM_FILE_PATH');
		$upload->subName       = array('date', 'Ym');
		$upfinfo = $upload->upload();
		if($upfinfo) {
			foreach($upfinfo as $k=>&$file){
				$file['fullpath'] = $upload->rootPath.$file['savepath'].$file['savename'];
			}
			$ret['error'] = false;
			$ret['result'] = $upfinfo;
		}else{
			$ret['error'] = true;
			$ret['result'] = $upload->getError();
			$this->error($upload->getError());
		}
		return $ret;
	}
}
