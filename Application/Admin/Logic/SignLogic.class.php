<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Admin\Logic;


class SignLogic extends \Think\Model{

    private $Usersign;

    public function __construct(){
        $this->Usersign = M('User_sign');
        $this->User = M('User');
    }

    public function getSignUsersList($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->Usersign->join('left join pl_user on pl_user_sign.userid = pl_user.uid')->group('userid')->where($mycond)->page($pstr)->select();
        return $data;
    }

    public function getSignUsersTotal($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $num = count($this->Usersign->group('userid')->where($mycond)->select());
        return $num;
    }

    public function getSignRecord($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->Usersign->join('left join pl_user on pl_user_sign.userid = pl_user.uid')->where($mycond)->page($pstr)->select();
        return $data;
    }

    public function getSignRecordTotal($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $num = $this->Usersign->where($mycond)->count();
        return $num;
    }

    public function getSignScore($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $num = $this->User->where($mycond)->select();
        return $num;
    }

}