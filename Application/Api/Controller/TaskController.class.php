<?php
namespace Api\Controller;
use Think\Controller;

class TaskController extends Controller {

    private $TaskLogic;
    private $Usertask;
	private $UserLogic;

    public function __construct(){
        parent::__construct();
        $this->TaskLogic = D('Task','Logic');
        $this->Usertask = M('User_task');
	    $this->UserLogic = D('User','Logic');
    }

	/**
	 * 分页获取任务列表
	 * 参数：
	 * @param int pages :（可选）分页，默认为1
	 * @param int tasktype: 1:日常任务 2:新手任务 3.应用任务 默认为1:日常任务
	 * @param int rowcount （可选）每页返回记录数 默认为系统设置值
	 * @param int userid :用户ID
	 * @return json : list
	 * */
	public function GetTasks(){
		$tasktype =  I('post.tasktype',null,'int');
		if(!in_array($tasktype,array(2,3))){
			$tasktype = 46;
		}else if($tasktype == 2){
			$tasktype = 47;
		}else{
			$tasktype = 48;
		}

		$pages = I('post.pages',null,'int')?I('post.pages',null,'int'):1;
		$rowcount = I('post.rowcount',null,'int')?I('post.rowcount',null,'int'):0;
		$userid = I('post.userid',null,'int');
		if(!isset($userid)){
			$data['rst'] = '-1';
			$data['msg'] = '参数不合法';
			$this->ajaxReturn($data);
		}
		$user = $this->UserLogic->getUserInfo($userid);
		if(!$user){
			$data['rst'] = '-1';
			$data['msg'] = '不存在该用户';
			$this->ajaxReturn($data);
		}
//      $userid = 1001;
		$data = $this->TaskLogic->getTaskList($userid,$tasktype,$pages,$rowcount);
		$this->ajaxReturn($data);
	}


	public function getTaskStatus(){
        $userid = I('post.userid');
        $taskid = I('post.taslid');
//        $userid = 1001;
//        $taskid = 1046;
        $data = $this->TaskLogic->getTaskRecord($userid,$taskid);
        if($data){
            $data['rst'] = 0;
            $data['msg'] = 'Get info successes';
        }else{
            $data['rst'] = -1;
            $data['msg'] = 'Get info failed';
        }
        $this->ajaxReturn($data);
    }


    public function CompleteTask(){
        $userid = I('post.userid');
        $taskid = I('post.taskid');
        //$status = I('post.status');
//        $userid = 1001;
//        $taskid = 1046;
        $nums = $this->Usertask->where('userid = '.$userid)->count();
        if($nums){
            $data = $this->TaskLogic->addRecord($userid,$taskid);
        }else{
            $data = $this->TaskLogic->getScoreByTask($userid,$taskid);
        }
        $this->ajaxReturn($data);
    }




    public function GetUserTaskRecord(){
        $userid = I('post.userid');
//        $userid = 1001;
        $data = $this->TaskLogic->getUserTaskRecord($userid);
        if($data){
            $data['rst'] = 0;
            $data['msg'] = 'Get info successes';
        }else{
            $data['rst'] = -1;
            $data['msg'] = 'Get info failed';
        }
        $this->ajaxReturn($data);
    }


}