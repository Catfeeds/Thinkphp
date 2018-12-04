<?php
namespace Admin\Controller;
use Think\Controller;
class SignController extends Controller {

    private $SignLogic;
    public function __construct(){
        parent::__construct();
        $this->SignLogic = D('Sign','Logic');
    }

    public function index(){
        echo 'Hello World';
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
        //$this->checkPriv('9_1_1');
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

}