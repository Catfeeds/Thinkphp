<?php
namespace Admin\Controller;
use Think\Controller;
class MessageController extends Controller {

    private $MessageLogic;
    private $Message;
    public function __construct(){
        parent::__construct();
        $this->MessageLogic = D('Message','Logic');
        $this->Message = M('sysmsg');
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
	    $this->checkPriv('11_1_1');
        $p = getCurPage();
        $res = $this->MessageLogic->getMessageList(array(),$p,1);
        $this->data = $res;
        $this->total = $this->MessageLogic->getMessageTotal(1);
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

    public function addmessage(){
        $this->checkPriv('11_1_2');
        $this->assign('errcode', '0');
        $this->assign('act', 'add');
        if (I('post.act') == 'add') {
            $newdata = array(
                'title' => I('post.title'),
                'content' => I('post.content'),
              //  'creatime' => I('post.creatime'),
	            'creatime' => date('Y-m-d'),
                'status' => I('post.status')
            );
            $this->Message->add($newdata);
            $this->redirect('Message/index');
        } else {
            $this->title = '添加系统消息';
            $this->display("Message/messageedit");
        }
    }

    public function editmessage(){
        $this->checkPriv('11_1_3');
        $this->assign('act', 'edit');
        $this->assign('errcode', '0');
        if (I('post.act') == 'edit') {
            $id=I('post.id','','int');
            $newdata = array(
                'title' => I('post.title'),
                'content' => I('post.content'),
                //'creatime' => I('post.creatime'),
	            'operationtime' => date('Y-m-d H:i:s'),
                'status' => I('post.status')
            );
            $this->Message->where('id=' . $id)->save($newdata);
            $this->redirect('Message/index');
        } else {
            $id=I('get.id','','int');
            if ($id) {
                $this->data = $this->Message->getById($id);
                $this->title = '编辑系统消息';
                $this->display("Message/messageedit");
            } else {
                $this->error('无效记录');
            }
        }
    }

	public function delmessage(){
		$this->checkPriv('11_1_4');
		$id=I('get.id','','int');
		if($id){
			$this->Message->where('id='.$id)->delete();
			$this->redirect('Message/index');
		}else{
			$this->error('没有该记录');
		}
	}


	public function disclaimermgr(){
		$this->checkPriv('11_2_1');
		$p = getCurPage();
		$res = $this->MessageLogic->getMessageList(array(),$p,3);
		$this->data = $res;
		$this->total = $this->MessageLogic->getMessageTotal(3);
		$show = constructAdminPage($this->total);
		$this->assign('page',$show);
		$this->display();
	}

	public function adddisclaimer(){
		$this->checkPriv('11_2_2');
		$this->assign('errcode', '0');
		$this->assign('act', 'add');
		if (I('post.act') == 'add') {
			$newdata = array(
					'title' => I('post.title'),
					'content' => I('post.content'),
				//  'creatime' => I('post.creatime'),
					'creatime' => date('Y-m-d'),
					'status' => I('post.status'),
					'type' => '3'
			);
			$this->Message->add($newdata);
			$this->redirect('Message/disclaimermgr');
		} else {
			$this->title = '添加免责声明';
			$this->display("Message/disclaimeredit");
		}
	}

	public function editdisclaimer(){
		$this->checkPriv('11_2_3');
		$this->assign('act', 'edit');
		$this->assign('errcode', '0');
		if (I('post.act') == 'edit') {
			$id=I('post.id','','int');
			$newdata = array(
					'title' => I('post.title'),
					'content' => I('post.content'),
				//'creatime' => I('post.creatime'),
					'operationtime' => date('Y-m-d H:i:s'),
					'status' => I('post.status')
			);
			$this->Message->where('id=' . $id)->save($newdata);
			$this->redirect('Message/disclaimermgr');
		} else {
			$id=I('get.id','','int');
			if ($id) {
				$this->data = $this->Message->getById($id);
				$this->title = '编辑免责声明';
				$this->display("Message/disclaimeredit");
			} else {
				$this->error('无效记录');
			}
		}
	}

	public function deldisclaimer(){
		$this->checkPriv('11_2_4');
		$id=I('get.id','','int');
		if($id){
			$this->Message->where('id='.$id)->delete();
			$this->redirect('Message/disclaimermgr');
		}else{
			$this->error('没有该记录');
		}
	}

	public function agreementmgr(){
		$this->checkPriv('11_3_1');
		$p = getCurPage();
		$res = $this->MessageLogic->getMessageList(array(),$p,4);
		$this->data = $res;
		$this->total = $this->MessageLogic->getMessageTotal(4);
		$show = constructAdminPage($this->total);
		$this->assign('page',$show);
		$this->display();
	}

	public function addagreement(){
		$this->checkPriv('11_3_2');
		$this->assign('errcode', '0');
		$this->assign('act', 'add');
		if (I('post.act') == 'add') {
			$newdata = array(
					'title' => I('post.title'),
					'content' => I('post.content'),
				//  'creatime' => I('post.creatime'),
					'creatime' => date('Y-m-d'),
					'status' => I('post.status'),
					'type' => '4'
			);
			$this->Message->add($newdata);
			$this->redirect('Message/agreementmgr');
		} else {
			$this->title = '添加服务协议';
			$this->display("Message/agreementedit");
		}
	}

	public function editagreement(){
		$this->checkPriv('11_3_3');
		$this->assign('act', 'edit');
		$this->assign('errcode', '0');
		if (I('post.act') == 'edit') {
			$id=I('post.id','','int');
			$newdata = array(
					'title' => I('post.title'),
					'content' => I('post.content'),
				//'creatime' => I('post.creatime'),
					'operationtime' => date('Y-m-d H:i:s'),
					'status' => I('post.status')
			);
			$this->Message->where('id=' . $id)->save($newdata);
			$this->redirect('Message/agreementmgr');
		} else {
			$id=I('get.id','','int');
			if ($id) {
				$this->data = $this->Message->getById($id);
				$this->title = '编辑服务协议';
				$this->display("Message/agreementedit");
			} else {
				$this->error('无效记录');
			}
		}
	}

	public function delagreement(){
		$this->checkPriv('11_3_4');
		$id=I('get.id','','int');
		if($id){
			$this->Message->where('id='.$id)->delete();
			$this->redirect('Message/agreementmgr');
		}else{
			$this->error('没有该记录');
		}
	}

	public function feedbackmgr(){
		$this->checkPriv('11_4_1');
		$p = getCurPage();
		$res = $this->MessageLogic->getMessageList(array(),$p,2);
		$this->data = $res;
		$this->total = $this->MessageLogic->getMessageTotal(2);
		$show = constructAdminPage($this->total);
		$this->assign('page',$show);
		$this->display();
	}

	public function editfeedback(){
		$this->checkPriv('11_4_2');
		$this->assign('act', 'edit');
		$this->assign('errcode', '0');
		if (I('post.act') == 'edit') {
			$id=I('post.id','','int');
			$newdata = array(
				'operationtime' => date('Y-m-d H:i:s'),
				'status' => I('post.status')
			);
			$this->Message->where('id=' . $id)->save($newdata);
			$this->redirect('Message/feedbackmgr');
		} else {
			$id=I('get.id','','int');
			if ($id) {
				$this->data = $this->Message->getById($id);
				$this->title = '处理意见反馈';
				$this->display("Message/feedbackedit");
			} else {
				$this->error('无效记录');
			}
		}
	}

	public function  delfeedback(){
		$this->checkPriv('11_4_3');
		$id=I('get.id','','int');
		if($id){
			$this->Message->where('id='.$id)->delete();
			$this->redirect('Message/feedbackmgr');
		}else{
			$this->error('没有该记录');
		}
	}

}