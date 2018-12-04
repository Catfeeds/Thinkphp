<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Admin\Logic;


class CornerLogic extends \Think\Model{
	public function __construct(){
		$this->Corner = M('Corner');
	}
	private $Corner;

	//根据角标类型获取角标
	public function getTypeCorner($params=array()){
		$params['status'] =  1;
		$params['isdel'] = array('exp','is null');
		$data = $this->Corner->where($params)->select();
		return $data;
	}

	//获取所有角标
	public function getCornerList($params=array(), $p){
		$params['isdel'] = array('exp','is null');
		$pstr = $p.','.C('ADMIN_REC_PER_PAGE');

		$data = $this->Corner->where($params)->page($pstr)->order('creatime desc')->select();
		return $data;
	}

	//获取角标总数
	public function getTotalCorner($params=array()){
		$params['isdel'] = array('exp','is null');
		$total =  $this->Corner->where($params)->count();
		return $total;
	}



}
