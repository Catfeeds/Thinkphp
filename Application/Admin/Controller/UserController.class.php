<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 16/05/07
 * Time: 上午9:02
 */
namespace Admin\Controller;
use Think\Controller;
class UserController extends Controller {
    private $UserLogic;
	private $Boperation;
    public function __construct(){
        parent::__construct();
        $this->UserLogic = D('User','Logic');
	    $this->Boperation = D('Boperation','Logic');
    }
	private function checkPriv($priv)
	{
		$adminid = session('adminid');
		if (empty($adminid)) $this->redirect('Adminuser/login', 0);
		if (!session('issuper')) {
			if (!empty($priv) && !in_array($priv, session('privs'))) $this->error('您没有此权限!.');
		}
		$this->assign('adname', session('name'));
	}


    public function usermgr(){
	    $this->checkPriv('8_1_4');
        $p = getCurPage();
        $res = $this->UserLogic->getUserList(array(),$p);
        $this->data = $res;
        $this->total = $this->UserLogic->getUserTotal();
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

    public function usersignmgr(){
        $p = getCurPage();
        $res = $this->SignLogic->getSignUsersList(array(),$p);
        $this->data = $res;
        $this->total = $this->SignLogic->getSignUsersTotal();
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

    public function signrecord(){
        $this->checkPriv('9_1_1');
        $uid = I('get.uid');
        $p = getCurPage();
        $res = $this->SignLogic->getSignRecord(array('userid'=>$uid),$p);
        $resscore = $this->SignLogic->getSignScore(array('uid'=>$uid));

        $this->data = $res;
        $this->total = $this->SignLogic->getSignRecordTotal(array('userid'=>$uid));
        $show = constructAdminPage($this->total);
        $this->assign('totalscore',$resscore[0]['totalscore']);
        $this->assign('page',$show);
        $this->display();
    }

	public function userOperation(){
		$p = getCurPage();
		$localid = I('get.localid','','int');
		$belongid = I('get.belongid','','int');
		$type =  I('get.type','','int');
		if($type == 1){
			$this->checkPriv('8_1_1');
			$type = C('RES_TYPE_APP');//下载记录
		}else if($type == 2){
			$this->checkPriv('8_1_2');
			$type =  C('RES_TYPE_VIDEO');//播放记录
		}else if($type == 3){
			$this->checkPriv('8_1_3');
			$type = C('RES_TYPE_BOOK');//阅读记录
		}
		$result = $this->UserLogic->userOperation($localid,$belongid,$type,$p);
		$this->data = $result['data'];
		$this->total = $result['totalcount'];
		$show = constructAdminPage($this->total);
		$this->assign('page',$show);
		$this->display('User/useroperation');

	}

}