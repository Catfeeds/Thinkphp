<?php

namespace Api\Logic;
use Think\Model;

class ConfigLogic extends Model{
	private $User;

	public function __construct(){
		$this->User =  M('User');
	}

	/*
	 * 获取系统设定
	 * @param int uid
	 * @return array data
	 */
	public function getSysSetting($uid){
		$data = $this->User->where('uid='.$uid)->field('shownickname showusername,showphone showphoneNo')->select();
		return $data;
	}
}
