<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Api\Logic;


class SignLogic extends \Think\Model{

       private $User_sign;
	private $UserTask;
	private $User;
    public function __construct(){
        $this->User_sign = M('User_sign');
	    $this->UserTask = M('UserTask');
	    $this->User = D('User','Logic');
    }

    public function getSignRecordByUid($cond=array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $data = $this->User_sign->where($mycond)->order('signtimes desc')->select();
        return $data;
    }

    public function getSignRecord($userid){
        $data = $this->User_sign->where(array('userid'=>$userid))->select();
        return $data;
    }

	public function saveSingTask($userid,$taskid,$score){
		$cond['userid'] = $userid;
		$timestart = date('Y-m-01 00:00:00');
		$timeend =  date('Y-m-d 00:00:00',strtotime("$timestart +1month"));
		$cond['finishtimes'] =  array('between',"$timestart,$timeend");
		$cond['taskid'] = $taskid;
 		$result =  $this->UserTask->where($cond)->find();
		if(!$result){
			unset($cond['finishtimes']);
			$cond['finishtimes'] = date('Y-m-d H:i:s');
			$this->UserTask->add($cond);
			$this->User->updateTotalScore($userid,$score);
		}
	}
}
