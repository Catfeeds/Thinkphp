<?php
namespace Api\Controller;
use Think\Controller;
class ConfigController extends Controller{
    private $Config;
    public function __construct()
    {
		$this->Config = D('Config','Logic');
    }


    /*
     * 获取系统设定
     * @param int userid:用户ID
     * @return json data
     */
	public function getSysSetting(){
		$uid = I('post.userid',null,'int');
		$data = array();
		$data = C('GetSysSetting');
		if(isset($uid)){
			$result =  $this->Config->getSysSetting($uid);
			if($result){
				$data = (array_merge($data,$result[0]));
			}
		}
		$this->ajaxReturn($data);
	}
}