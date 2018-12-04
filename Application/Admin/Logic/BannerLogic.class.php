<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Admin\Logic;


class BannerLogic extends \Think\Model{
    public function __construct(){
        $this->Banner = M('Banner');
        $this->position = M('Position');
    }
    private $Banner;
    private $position;

    public function getBannerTotal($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        //$mycond['end_time']=array('gt',date('Y-m-d H:i:s'));
        $num = $this->Banner->where($mycond)->where('isdel is null')->count();
        return $num;
    }

    public function getBannerPid($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        //$data = $this->Banner->where($mycond)->where('isdel is null')->page($pstr)->order("if(end_time='0000-00-00 00:00:00',0,1),end_time desc")->select();
	    $data = $this->position->where($mycond)->select();
	    return $data;
    }

    public function getBannerList($cond=array(), $p){
        $mycond = array();
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        //$data = $this->Banner->where($mycond)->where('isdel is null')->page($pstr)->order("if(end_time='0000-00-00 00:00:00',0,1),end_time desc")->select();
        $data = $this->Banner->where($mycond)->where('isdel is null')->page($pstr)->order("sort desc")->select();
        return $data;
    }

    public function getBannerById($id){
        if($id){
            $data = $this->Banner->getById($id);
            return $data;
        }else{
            return false;
        }
    }

    public function getUuid(){
        $uuid=genUuid();
        $con['uuid']=$uuid;
        $ret=$this->Video->where($con)->select();
        if(!$ret){
            return $uuid;
        }else {
            $uuid=genUuid();
            return $uuid;
        }
    }


}
