<?php
namespace Api\Logic;


class AddressLogic extends \Think\Model{
	public function __construct(){
		$this->Province =  M('Province');
		$this->City =  M('City');
		$this->District =  M('District');
	}

	private $Province;
	private $City;
	private $District;

	public function getPCD($type,$id=0){
		if($type == 1 || $id == 0){
			$data = $this->Province->field('id,province')->select();
		}else if($type == 2){
			$data =  $this->City->field('id,city')->where(array('province_id'=>$id))->select();
		}else{
			$data =  $this->District->field('id,district')->where(array('city_id'=>$id))->select();
		}
		return $data;
	}
}