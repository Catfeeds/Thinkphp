<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Api\Logic;


class IndexFunctionLogic extends \Think\Model{
    public function __construct(){
        $this->IndexFuction = M('Function');
	   // $this->Operation =  M('Operation');
	    $this->Boperation = M('Boperation');
	    $this->Sysmsg = M('Sysmsg');
    }
    private $IndexFuction;
	//private $Operation;
	private $Boperation;
	private $Sysmsg;

    public function getFunctionTotal($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $num = $this->IndexFuction->where($mycond)->where('isdel is null')->count();
        return $num;
    }

	/*
    public function getFunctionList($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->IndexFuction->where($mycond)->where('isdel is null')->page($pstr)->order('id asc')->select();
        return $data;
    }*/

	public function getFunctionList(){
		$data = $this->IndexFuction->where('isdel is null')->field('modelname,modeldesc')->select();
		foreach($data as $key=>$value){
			$desc =  explode(';',$value['modeldesc']);
			$data[$key]['modeldesc'] = $desc[rand(0,(count($desc)-1))];
		}
		return $data;
	}

    public function getFunctionById($id){
        if($id){
            $data = $this->IndexFuction->getById($id);
            return $data;
        }else{
            return false;
        }
    }

	/**
	 * 系统消息检查（$type）
	 *
	 * @return  array : data
	 */
	public function checkNotices($params = array(),$type = 1){
		$cont['status'] = '1';
		$cont['type'] = '1';
		//获取所有有效系统消息
		$data = $this->Sysmsg->field('id,title,content,creatime date')->where($cont)->select();
		//未读新消息数量num
		$num = 0;
		//迭代判断该用户是否已读该消息
		$params['operation'] = '5';
		foreach($data as $key=>$value){
			$params['objid'] = $value['id'];
			if($this->Boperation->where($params)->count() > 0){
				$data[$key]['isread'] = '1';//已读
			}else{
				$data[$key]['isread'] = '0';
				$num += 1;
			}
		}
		if($type == 1){
			return $data;
		}else{
			return $num;
		}
	}

	/**
	 * 获取系统消息
	 *
	 * @return  array : data
	 */
	public function getNotices($params = array(),$pages,$rowcount){
		if($rowcount>0 && $rowcount<C('MOB_REC_PER_PAGE')){
			$page = $pages.','.$rowcount;
		}else{
			$page = $pages.','.C('MOB_REC_PER_PAGE');
		}

		$cont['status'] = '1';
		$cont['type'] = '1';
		//获取所有有效系统消息
		$data = $this->Sysmsg->field('id,title,content,creatime date')->where($cont)->page($page)->select();

		//迭代判断该用户是否已读该消息
		$params['operation'] = '5';
		foreach($data as $key=>$value){
			$params['objid'] = $value['id'];
			if($this->Boperation->where($params)->count() > 0){
				$data[$key]['isread'] = '1';//已读
			}else{
				$data[$key]['isread'] = '0';
			}
		}
		$totalcount =  $this->Sysmsg->where($cont)->count();
		return array_merge(array('totalcount'=>$totalcount),array('list'=>$data));

	}

	/**
	 * 意见收集
	 * @param array params
	 *
	 * @return int data :操作结果
	 */
	public function collectComments($params){
		$params['type'] = '2';
		$data = $this->Sysmsg->data($params)->add();
		return $data;
	}

	/*
	 * 免责和申明
	 * @param int params :0.免责声明 2.服务协议
	 * @return array data
	 */
	public function getStatement($params){
		$data = $this->Sysmsg->where($params)->field('content statements')->select();
		return $data;
	}
}
