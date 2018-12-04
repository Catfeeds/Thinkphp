<?php
namespace Admin\Controller;
use Think\Controller;
header("Content-Type:text/html; charset=utf-8");
class BookController extends Controller {
    public function __construct(){
        parent::__construct();
        $this->bookLogic =  D('Book','Logic');
        $this->readrecordLogic =  D('Readrecord','Logic');
        $this->categoryLogic =  D('Category','Logic');
        $this->Book=  M('Book');
        $this->Readrecord=  M('Readrecord');
        $this->Bookparam=  M('Bookparam');
    }

    private $bookLogic ;
    private $readrecordLogic ;
    private $Book ;
    private $Readrecord ;
    private $Bookparam;

    private function checkPriv($priv){
        $adminid = session('adminid');
        if(empty($adminid)) $this->redirect('Adminuser/login',0);
        if(!session('issuper')){
            if(!empty($priv) && !in_array($priv,session('privs'))) $this->error('您没有此权限!.');
        }
        $this->assign('adname', session('name'));
    }

    public function bookmgr(){
        $this->checkPriv('2_1_1');
        $p = getCurPage();
        $where = array('pid'=>'0');
        $res = $this->bookLogic->getBookList($where,$p);
        foreach($res as $k=> $v){
            $cates = $this->categoryLogic->getCategoryById($v['categoryid']);
            $res[$k]['cate'] = $cates['name'];
        }
        $this->data = $res;
        $this->total = $this->bookLogic->getBookTotal($where);
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

    public function addbook(){
        $this->checkPriv('2_1_2');
        $this->assign('act','add');
        $this->assign('errcode','0');
        if(I('post.act')=='add'){
            $newdata = array();
            $newdata['name'] = I('post.name');
            $newdata['bookno'] = I('post.bookno');
            //$newdata['authorno'] = I('post.authorno');
            $newdata['author'] = I('post.author');
            //$newdata['sort'] = I('post.sort');
            $newdata['press'] = I('post.press');
            $newdata['categoryid'] = I('post.categoryid');
	        $newdata['describe'] = I('post.describe');

	        if($_FILES['img']['size']>0 ||$_FILES['cover']['size']>0){
		        $upres = $this->upimgfile();
		        if($upres['error'] == false){
			        if(!empty($upres['result']['img']['fullpath'])){
				        $newdata['img'] = $upres['result']['img']['fullpath'];
			        }
			        if(!empty($upres['result']['cover']['fullpath'])){
				        $newdata['cover'] = $upres['result']['cover']['fullpath'];
			        }
		        }
	        }
			$newdata['creatime'] = date('Y-m-d H:i:s');
           // $ret = $this->Book->add(array('status'=>'1'));
           // $newdata['bookid'] = $ret;
           // $newdata['createdate'] = date('Y-m-d H:i:s');
	        $a =  array_keys(array_map('trim',$newdata),'');
	        if($a){
		        $this->error('带*号必填项目不能为空');
	        }

            $data = $this->Book->add($newdata);
            if($data){
                $this->redirect('Book/bookmgr');
            }else{
                $this->error('插入数据错误');
            }
        }else{
            //$cates = getSortedCategory($this->categoryLogic->getParentCategoryList('32'));
	        $cates = $this->categoryLogic->getParentCategoryList(C('BOOK_CATEGORY'));//图书分类
            $this->assign('cate',$cates);
            $this->display("Book/bookedit");
        }
    }

    public function editbook(){

        $this->checkPriv('2_1_3');
        $this->assign('act','edit');
        $this->assign('errcode','0');
        if(I('post.act')=='edit'){
//            var_dump($_FILES);die;
            $newdata = array();
            $id = I('post.id','','int');
            $newdata['name'] = I('post.name');
            $newdata['bookno'] = I('post.bookno');
            //$newdata['authorno'] = I('post.authorno');
            $newdata['author'] = I('post.author');
           // $newdata['sort'] = I('post.sort');
            $newdata['press'] = I('post.press');
            $newdata['categoryid'] = I('post.categoryid');
			$newdata['describe'] = I('post.describe');

	        if($_FILES['img']['size']>0 ||$_FILES['cover']['size']>0){
		        $upres = $this->upimgfile();
		        if($upres['error'] == false){
			        if(!empty($upres['result']['img']['fullpath'])){
				        $newdata['img'] = $upres['result']['img']['fullpath'];
			        }
			        if(!empty($upres['result']['cover']['fullpath'])){
				        $newdata['cover'] = $upres['result']['cover']['fullpath'];
			        }
		        }
	        }

	        $a =  array_keys(array_map('trim',$newdata),'');
	        if($a){
		        $this->error('带*号必填项目不能为空');
	        }

            $ret = $this->Book->where('id='.$id)->save($newdata);
	        if($ret !== false){
		        $this->redirect('Book/bookmgr');
	        }else{
				$this->error('修改失败',U('Book/bookmgr',5));
	        }

        }else{

            $id = I('get.id','','int');
            $data = $this->bookLogic->getBookById($id);
            $this->data = $data;
          //  $cates = getSortedCategory($this->categoryLogic->getCategoryList());
	        $cates = $this->categoryLogic->getParentCategoryList(C('BOOK_CATEGORY'));
            $this->assign('cate',$cates);
            $this->display("Book/bookedit");
        }
    }

	public function checkslide(){
		$this->checkPriv('2_1_7');
		$id = I('get.id','','int');
		if($id){
			$data['slide'] = I('get.slide','','int');
			$this->Book->where('id='.$id)->save($data);
			$from = I('server.HTTP_REFERER');
			redirect($from);
		}else{
			$this->error('无效的操作');
		}
	}

    public function delbook(){
        $this->checkPriv('2_1_4');
        $id = I('get.id','','int');
        if($id){
            $data['isdel']= date("Y-m-d H:i:s");;
            $this->Book->where('id='.$id)->save($data);
            $from = I('server.HTTP_REFERER');
            redirect($from);
        }else{
            $this->error('该记录不存在');
        }
    }

    public function chgbookstatus(){
	    $this->checkPriv('2_1_6');
        $id = I('get.id','','int');
        $status = I('get.status','','int');
        if($id){
            if($status == 1){
                $this->Book->where('id='.$id)->save(array('status'=>1));
                $from = I('server.HTTP_REFERER');
                redirect($from);
            }else if($status == 0){
                $this->Book->where('id='.$id)->save(array('status'=>0));
                $from = I('server.HTTP_REFERER');
                redirect($from);
            }else{
                $this->error('无该状态设置');
            }
        }else{
            $this->error('该记录不存在');
        }
    }

    public function chaptermgr(){
        $this->checkPriv('2_1_5');
        $p = getCurPage();
        $where = array('bookid'=>I('get.bookid'));
        $res = $this->bookLogic->getChapterListById($where,$p);
        $this->data = $res;
        $this->total = $this->bookLogic->getChapterTotalById($where);
        $show = constructAdminPage($this->total);
        $this->assign('bookid',I('get.bookid'));
        $this->assign('page',$show);
        $this->display();
    }

    public function addchapter(){
        $this->assign('act','add');
        $this->assign('errcode','0');

        if(I('post.act')=='add'){
            $newdata = array();
            $newdata['chapter'] = I('post.chapter');
            $newdata['chapteraddress'] = I('post.chapteraddress');
            $newdata['chaptercontent'] = I('post.chaptercontent');
            $newdata['sort'] = I('post.sort');
            $newdata['chaptertitle'] = I('post.chaptertitle');
           // $newdata['pid'] = I('post.bid');
           // $res = $this->bookLogic->getBookParamById(I('post.bid'));
          //  $newdata['bookid'] = $res['bookid'];
	        $newdata['bookid'] = I('post.bookid','','int');
            $newdata['createdate'] = date('Y-m-d H:i:s');
            $data = $this->Bookparam->add($newdata);
            if($data){
                $this->redirect('Book/chaptermgr',array('bookid'=>$newdata['bookid']));
            }else{
                $this->error('插入数据错误');
            }
        }else{
            //$id = I('get.id');
	        $bookid = I('get.bookid','','int');
            $data1 = $this->bookLogic->getBookParamById($bookid);
            $data['pname'] = $data1['name'];
	        $data['bookid'] = $data1['id'];
            $this->data = $data;
            $cate = $this->categoryLogic->getCategoryById($data1['categoryid']);
            $this->assign('cate',$cate);
            $this->display("Book/chapteredit");
        }
    }

    public function editchapter(){
        $this->assign('act','edit');
        $this->assign('errcode','0');
        if(I('post.act')=='edit'){
            $newdata = array();
            $id = I('post.id');
            $bookid = I('post.bookid');
            $newdata['chapter'] = I('post.chapter');
            $newdata['chapteraddress'] = I('post.chapteraddress');
            $newdata['chaptercontent'] = I('post.chaptercontent');
            $newdata['sort'] = I('post.sort');
            $newdata['chaptertitle'] = I('post.chaptertitle');

           // $data = $this->bookLogic->getBookParamById(I('post.id'));
            //$newdata['bookid'] = $data['bookid'];
            $newdata['modifydate'] = date('Y-m-d H:i:s');
            $ret = $this->Bookparam->where('id='.$id)->save($newdata);
            if($ret !== false){
                $this->redirect('Book/chaptermgr',array('bookid'=>$bookid));
            }else{
                $this->assign('errcode','1');  // 修改失败
                $this->error('编辑数据失败');
            }
        }else{
            $id = I('get.id','','int');
           // $pid = I('get.pid');
	        $bookid = I('get.bookid','','int');
            $data = $this->bookLogic->getChapterParamById($id);
            $res = $this->bookLogic->getBookParamById($bookid);
            $data['pname'] = $res['name'];
            $cate = $this->categoryLogic->getCategoryById($res['categoryid']);
            $this->data = $data;
            $this->assign('cate',$cate);
            $this->display("Book/chapteredit");
        }
    }

	//改变章节上下架状态
	public function changechapter(){
		$id = I('get.id','','int');
		$status = I('get.status','','int');
		if($id){
			if($status == 1){
				$this->Bookparam->where('id='.$id)->save(array('status'=>1));
				$from = I('server.HTTP_REFERER');
				redirect($from);
			}else if($status == 0){
				$this->Bookparam->where('id='.$id)->save(array('status'=>0));
				$from = I('server.HTTP_REFERER');
				redirect($from);
			}else{
				$this->error('无该状态设置');
			}
		}else{
			$this->error('该记录不存在');
		}

	}

    public function delchapter(){
        $id = I('get.id','','int');
        if($id){
            $data['isdel']= date("Y-m-d H:i:s");;
            $this->Bookparam->where('id='.$id)->save($data);
            $from = I('server.HTTP_REFERER');
            redirect($from);
        }else{
            $this->error('该记录不存在');
        }
    }

    public function readrecordmgr(){
        $this->checkPriv('1_3_1');
        $p = getCurPage();
        $where = array();
        $res = $this->bookLogic->getRecordList($where,$p);
        foreach($res as $k=>$v){
            $res[$k]['username']=session('username');
            $ks = $this->bookLogic->getBookParamByBookId($v['bookid']);
            $res[$k]['bookname'] = $ks['name'];
        }
        $this->data = $res;
        $this->total = $this->bookLogic->getRecordTotal();
        $show = constructAdminPage($this->total);
        $this->assign('id',I('get.id'));
        $this->assign('page',$show);
        $this->display();
    }

    public function readrecord(){
        $id = I('get.id');
        if($id){
            $data = $this->bookLogic->getRecordById($id);
            if($data&&$data['uid']==session('adminid')){
                $newdata['hits'] = $data['hits']+1;
                $ret = $this->Readrecord->where('id='.$data['id'])->save($newdata);
                if($ret){
                    $this->redirect('Book/readrecordmgr');
                }else{
                    $this->error('插入数据错误');
                }
            }else{
                $data = $this->bookLogic->getChapterParamById($id);
                $newdata['bookid'] = $data['bookid'];
                $newdata['chapter'] = $data['chapter'];
                $newdata['paramid'] = $id;
                $newdata['hits'] = 1;
                $newdata['uid'] = session('adminid');
                $newdata['createdate'] = date('Y-m-d H:i:s');
                $res =  $this->Readrecord->add($newdata);
                if($res){
                    $this->redirect('Book/readrecordmgr');
                }else{
                    $this->error('插入数据错误');
                }
            }
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



    private function upfile(){
        $ret = array();
        $upload =  new \Think\Upload();
        $upload->maxSize       = C('ITEM_FILE_SIZE');;
        $upload->exts          = array('zip','jar','apk','doc','xls');
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