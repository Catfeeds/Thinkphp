<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Admin\Logic;


class MessageLogic extends \Think\Model{

    private $Message;

    public function __construct(){
        $this->Message = M('sysmsg');
    }

    public function getMessageList($cond=array(), $p,$type){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
	    $mycond['type'] = $type;
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->Message->where($mycond)->page($pstr)->order('creatime desc')->select();
        return $data;
    }

    public function getMessageTotal($type,$cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
	    $mycond['type'] = $type;
        $num = $this->Message->where($mycond)->count();
        return $num;
    }
//
//    public function getTastCategory(){
//        $mycond =array('pid'=>1);
//        $data = M('Category')->where($mycond)->select();
//        return $data;
//    }
//
//    public function getUserTaskList($cond=array(), $p){
//        $mycond = array();
//        if(is_array($cond) && count($cond)>0){
//            $mycond = $cond;
//        }
//        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
//        $data = $this->UserTask->join('left join pl_admin on pl_user_task.userid = pl_admin.uid')->group('userid')->where($mycond)->page($pstr)->select();
//        return $data;
//    }
//
//    public function getUserTaskTotal($cond = array()){
//        $mycond = array();
//        if(is_array($cond) && count($cond)>0){
//            $mycond = $cond;
//        }
//        $num = count($this->UserTask->group('userid')->where($mycond)->select());
//        return $num;
//    }
//
//    public function getRecordByUid($cond=array(), $p){
//        $mycond = array();
//        if(is_array($cond) && count($cond)>0){
//            $mycond = $cond;
//        }
//        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
//        $data = $this->UserTask->join('left join pl_admin on pl_user_task.userid = pl_admin.uid')->join('left join pl_task on pl_user_task.taskid = pl_task.id')->where($mycond)->page($pstr)->select();
//        return $data;
//    }
//
//    public function getRecordTotalByUid($cond = array()){
//        $mycond = array();
//        if(is_array($cond) && count($cond)>0){
//            $mycond = $cond;
//        }
//        $num = $this->UserTask->where($mycond)->count();
//        return $num;
//    }

}