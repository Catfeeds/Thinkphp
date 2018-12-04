<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Admin\Logic;


class TaskLogic extends \Think\Model{

    private $Task;

    public function __construct(){
        $this->Task = M('Task');
        $this->UserTask = M('User_task');
    }

    public function getTaskList($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->Task->join('left join pl_category on pl_category.id = pl_task.categoryid')->field('pl_task.id,pl_task.title,pl_task.content,pl_task.score,pl_task.starttimes,pl_task.endtimes,pl_category.name')->where($mycond)->page($pstr)->select();
        return $data;
    }

    public function getTaskTotal($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $num = $this->Task->where($mycond)->count();
        return $num;
    }

    public function getTastCategory(){
        $mycond =array('pid'=>44);
        $data = M('Category')->where($mycond)->select();
        return $data;
    }

    public function getUserTaskList($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->UserTask->join('left join pl_admin on pl_user_task.userid = pl_admin.uid')->group('userid')->where($mycond)->page($pstr)->select();
        return $data;
    }

    public function getUserTaskTotal($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $num = count($this->UserTask->group('userid')->where($mycond)->select());
        return $num;
    }

    public function getRecordByUid($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->UserTask->join('left join pl_admin on pl_user_task.userid = pl_admin.uid')->join('left join pl_task on pl_user_task.taskid = pl_task.id')->where($mycond)->page($pstr)->select();
        return $data;
    }

    public function getRecordTotalByUid($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $num = $this->UserTask->where($mycond)->count();
        return $num;
    }

}