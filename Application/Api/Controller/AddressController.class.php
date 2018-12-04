<?php
namespace Api\Controller;
use Think\Controller;
class AddressController extends Controller {
	public function __construct(){
		parent::__construct();
		$this->AddressLogic =  D('Address','Logic');
	}

	private $AddressLogic;

	/**
	 * 获取 privince city district
	 * @param int type :1:省份 2：市 3：区 默认为1：省份
	 * @parma int id :父级ID 如找市区  不传则为查找省份
	 */
	public function getPCD(){
		$type = I('post.type',null,'int');
		if(!in_array($type,array(2,3))){
			$type = 1;
		}
		$id = I('post.id',null,'int');
		$result = $this->AddressLogic->getPCD($type,$id);
		if($result !== false){
			if(count($result)>0){
				$this->ajaxReturn($result);
			}else{
				$data['id'] = 0;
				if($type == 1){
					$data['province'] = '无';
				}else if($type == 2){
					$data['city'] = '无';
				}else{
					$data['district'] = '无';
				}
				$this->ajaxReturn($data);
			}

		}else{
			$date['msg'] = '系统异常，请重新尝试';
			$data['rst'] = -1;
			$this->ajaxReturn($data);
		}
	}

}