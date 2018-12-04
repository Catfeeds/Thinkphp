<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Api\Logic;


class TaskLogic extends \Think\Model{

    private $User;
    private $Task;
    private $Usertask;
	private $UserSing;
	private $UserLogic;
	private $Boperation;

    public function __construct(){
        $this->User = M('User');
        $this->Task = M('Task');
        $this->Usertask = M('User_task');
	    $this->UserSign = M('UserSign');
	    $this->UserLogic =  D('User','Logic');
	    $this->Boperation =  M('Boperation');
    }

    public function getTaskListByCategoryId($userid,$categoryid){
        $data = $this->Task->where(array('categoryid'=>$categoryid))->field('id taskid,title,score,categoryid')->select();

        foreach($data as $key => $row){
            $taskid = $row['id'];
            if($this->Usertask->where(array('userid'=>$userid,'taskid'=>$taskid))->count() > 0){
                $data[$key]['status'] = 1;
            }else{
                $data[$key]['status'] = 0;
            }
        }
        return $data;
    }

	/**
	 * 获取任务列表
	 * @param $userid
	 * @return array data
	 */
    public function getTaskList($userid,$tasktype,$pages,$rowcount,$typee=1){
       // $result[0] = $this->getTaskListByCategoryId($userid,46);
       // $result[1] = $this->getTaskListByCategoryId($userid,47);
        //$result[2] = $this->getTaskListByCategoryId($userid,48);
	    if($rowcount>0 && $rowcount<C('MOB_REC_PER_PAGE')){
		    $curpage = $pages.','.$rowcount;
	    }else{
		    $curpage = $pages.','.C('MOB_REC_PER_PAGE');
	    }
	    $whe['task.categoryid'] = $tasktype;
	    $whe['_string'] = 'task.categoryid = category.id';

	    if($typee ==1){
		    $data = $this->Task->table('__TASK__ task,__CATEGORY__ category')->field('task.id taskid,task.categoryid,task.title,task.appid,task.score,category.title categoryname')
				    ->where($whe)->page($curpage)->select();
		    $totalcount = $this->Task->table('__TASK__ task,__CATEGORY__ category')->field('task.id taskid')->where($whe)->count();
	    }else if($typee=2){
		    $data =  $this->Task->field('id taskid,categoryid,title,appid,score')->select();
		    $totalcount =  count($data);
	    }

	    ///$user = $this->UserLogic->getUserInfo($userid);
	    $starttime = date('Y-m-d H:i:s',strtotime(date('Y-m-d')));
	    $endtime = date('Y-m-d H:i:s',strtotime(date('Y-m-d',strtotime('+1 days'))));
	    foreach($data as $key=> $row){
			$taskid = $row['taskid'];
		    if($taskid == C('task_qd')){
			    //日常任务 签到
			    $params['userid'] = $userid;
			    $params['signtimes'] = array('between',array($starttime,$endtime));
			    if($this->UserSign->where($params)->count() > 0){
					$data[$key]['status'] = 1;
			    }else{
				    $data[$key]['status'] = 0;
			    }
		    }else if( $row['taskid'] == C('task_fx')){
			    //日常任务 分享
			    $fenxiang['userid'] = $userid;
			    $fenxiang['operation'] = '7';
			    $fenxiang['creatime'] = array('between',array($starttime,$endtime));
			    $result = $this->Boperation->where($fenxiang)->count();
			    if($result > 0){
				    $data[$key]['status'] = 1;
			    }else{
				    $data[$key]['status'] = 0;
			    }

		    }else if( $row['categoryid'] == C('task_xs') || $row['categoryid'] == C('task_yy')){
			    //新手任务 应用任务
			    $result = $this->Usertask->where(array('userid'=>$userid,'taskid'=>$taskid))->count();
			    if($result >0){
				    $data[$key]['status'] = 1;
			    }else{
				    $data[$key]['status'] = 0;
			    }
		    }else{
			    //日常任务 连续签到
			    $timestart = date('Y-m-01 00:00:00');
			    $timeend =  date('Y-m-d 00:00:00',strtotime("$timestart +1month"));
			    $cond['finishtimes'] =  array('between',"$timestart,$timeend");
			    $cond['userid'] = $userid;
			    $cond['taskid'] = $taskid;
				if($this->Usertask->where($cond)->count() > 0){
					$data[$key]['status'] = 1;
				}else{
					$data[$key]['status'] = 0;
				}
		    }
	    }
        return array_merge(array('list'=>$data),array('totalcount'=>$totalcount));
    }

    public function getTaskRecord($userid,$taskid){
        $data = $this->Usertask->where(array('userid'=>$userid,'taskid'=>$taskid))->find();
        return $data;
    }

    public function getUserTaskRecord($userid){
        $data = $this->Usertask->where(array('userid'=>$userid))->select();
        return $data;
    }

    public function addRecord($userid,$taskid){
        $adddata = array(
            'userid' => $userid,
            'taskid' => $taskid,
            'finishtimes' => date('Y-m-d H:m:s',time())
        );
        $inserid = $this->Usertask->add($adddata);
        if($inserid){
            $result['msg'] = '001';
            $result['status'] = 1;
            $result['usertaskid'] = $inserid;
        }else{
            $result['msg'] = '002';
        }
        return $result;
    }

    public function getScoreByTask($userid,$taskid){
        $adddata = array(
            'userid' => $userid,
            'taskid' => $taskid,
            'finishtimes' => date('Y-m-d H:m:s',time())
        );
        $inserid = $this->Usertask->add($adddata);
        $result = array();
        if($inserid){
            $totalscore = $this->User->where('uid='.$userid)->getField('totalscore');
            $taskscore = $this->Task->where('id='.$taskid)->getField('score');
            $updateid = $this->User->where('uid='.$userid)->save(array('totalscore'=>$totalscore+$taskscore));
            if($updateid){
                $result['msg'] = '001';
                $result['status'] = 0;
                $result['usertaskid'] = $inserid;
                $result['totalscore'] = $totalscore+$taskscore;
            }else{
                $result['msg'] = '002';
            }
        }
        return $result;
    }

	public function getUserTask($cond = array()){
		$data =  $this->Usertask->where($cond)->count();
		return $data;
	}

	public function saveUserTask($cond = array()){
		$cond['finishtimes'] = date('Y-m-d H:i:s');
		$this->Usertask->add($cond);
	}

	public function getIdTask($cond = array()){
		$data =  $this->Task->where($cond)->find();
		return $data;
	}

}