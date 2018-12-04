<?php
namespace Admin\Controller;
use Think\Controller;
header("Content-Type:text/html; charset=utf-8");
class CommentController extends Controller {
    public function __construct(){
        parent::__construct();
        $this->commentLogic=  D('Comment','Logic');
        $this->dnsLogic=  D('Dns','Logic');
        $this->positionLogic =  D('Position','Logic');
        $this->bannerLogic =  D('Banner','Logic');
        $this->videoLogic =  D('Video','Logic');
        $this->appsLogic =  D('Apps','Logic');
        $this->Video=  M('Video');
        $this->Apps=  M('App');
        $this->Banner=  M('Banner');
        $this->Position=  M('Position');
        $this->Dns=  M('Dns');
        $this->Comment=  M('Comment');
	    $this->Category = M('Category');
    }

    private $commentLogic;
    private $dnsLogic;
    private $positionLogic ;
    private $bannerLogic ;
    private $Position ;
    private $Banner ;
    private $videoLogic ;
    private $Video ;
    private $appsLogic ;
    private $Apps ;
    private $Dns;
    private $Comment;
	private $Categroy;

    private function checkPriv($priv){
        $adminid = session('adminid');
        if(empty($adminid)) $this->redirect('Adminuser/login',0);
        if(!session('issuper')){
            if(!empty($priv) && !in_array($priv,session('privs'))) $this->error('您没有此权限!.');
        }
        $this->assign('adname', session('name'));
    }
    //comment
    public function commentmgr(){
        $this->checkPriv('6_1_0');
        $p = getCurPage();
        $res = $this->commentLogic->getCommentList(array(),$p);

        $this->total = $this->commentLogic->getCommentTotal();
        $show = constructAdminPage($this->total);

        $this->data = $res;
        $this->assign('page',$show);
        $this->display();
    }

    public function ajaxcommentscategory(){
        $type=I('post.type','','int')?I('post.type','','int'):1;
        $data = $this->videoLogic->getVideoTypeByType($type);
        $this->ajaxReturn($data);
    }

    public function ajaxcomments(){
        $type=I('post.type','','int')?I('post.type','','int'):1;
        $data = $this->videoLogic->getVideoByType($type);
        $this->ajaxReturn($data);
    }

    public function addcomment(){
        $this->checkPriv('1_3_2');
        $this->assign('act','add');
        $this->assign('errcode','0');
        if(I('post.act')=='add'){
	        $newdata = array();
	       // $newdata['type'] = I('post.type','','int');
	        $newdata['type'] = 2;
	        $newdata['score'] = I('post.score','','float');
	        $newdata['objid'] = I('post.objid','','int');
	        $newdata['content'] = I('post.content');
	        $newdata['audit'] = I('post.audit','','int');
            $ret = $this->Comment->add($newdata);
            if($ret){
                $this->redirect('Comment/commentmgr');
            }else{
                $this->error('插入数据错误');
            }
        }else{
	        $this->cate =  array(array('id'=>70,'name'=>'游戏'),array('id'=>71,'name'=>'应用'),array('id'=>1,'name'=>'视频'));
            $this->display("Comment/commentedit");
        }
    }

    public function editcomment(){
        $this->checkPriv('6_1_1');
        $this->assign('act','edit');
        $this->assign('errcode','0');
        if(I('post.act')=='edit'){
            $newdata = array();
            $id = I('post.id','','int');
            //$newdata['type'] = I('post.type','','int');
	        $newdata['type'] = 2;
            $newdata['score'] = I('post.score','','float');
            $newdata['objid'] = I('post.objid','','int');
            $newdata['content'] = I('post.content');
	        $newdata['audit'] = I('post.audit','','int');
            $ret = $this->Comment->where('id='.$id)->save($newdata);
	        if($ret  !== false){
		        $this->redirect('Comment/commentmgr');
	        }else{
		        $this->error('修改失败');
	        }
        }else{
            $id = I('get.id','','int');
            $data = $this->commentLogic->getCommentById($id);
            $this->data = $data;

	        $this->cate =  array(array('id'=>70,'name'=>'游戏'),array('id'=>71,'name'=>'应用'),array('id'=>1,'name'=>'视频'));
            $this->display("Comment/commentedit");
        }
    }

    public function delcomment(){
        $this->checkPriv('6_1_2');
        $id = I('get.id','','int');
        if($id){
            $data['isdel']= date("Y-m-d H:i:s");;
            $this->Comment->where('id='.$id)->save($data);
            $from = I('server.HTTP_REFERER');
            redirect($from);
        }else{
            $this->error('该记录不存在');
        }
    }
}