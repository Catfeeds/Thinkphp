<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 16/05/07
 * Time: 上午9:02
 */

namespace Admin\Logic;


class UserLogic extends \Think\Model{
    public function __construct(){
	    $this->Boperation = M('Boperation');
        $this->User = M('User');
        $this->ClouduserV = D('ClouduserView');
    }
    private $User;
    private $ClouduserV;
	private $Boperation;


    public function getUserTotal($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $num = $this->User->where($mycond)->count();
        return $num;
    }

    public function getUserList($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }

        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->ClouduserV->where($mycond)->page($pstr)->order('creatime desc')->select();
        return $data;
    }


    public function getVideoById($id){
        if($id){
            $data = $this->Video->getById($id);
            return $data;
        }else{
            return false;
        }
    }

	/**
	 * 查询用户操作记录
	 */
	public function userOperation($localid,$belongid,$type,$p){
		$page = $p.','.C('ADMIN_REC_PER_PAGE');
		$params['b.userid'] = $localid;
		$params['b.factory'] = $belongid;
		$params['b.operation'] = $type;
		if($type ==  C('RES_TYPE_APP')){
			$params['_string'] = 'b.objid=a.id';
			$data =  $this->Boperation->table('__BOPERATION__ b,__APP__ a')->where($params)
					->field('b.userid,b.creatime,b.factory,a.id resourceid,a.name')->order('b.creatime desc')->page($page)->select();
		}else if($type == C('RES_TYPE_VIDEO')){
			$params['_string'] = 'b.objid=v.id';
			$data =  $this->Boperation->table('__BOPERATION__ b,__VIDEO__ v')->where($params)
					->field('b.userid,b.creatime,b.factory,v.id resourceid,v.name')->order('b.creatime desc')->page($page)->select();
		}else if($type == C('RES_TYPE_BOOK')){
			$params['_string'] = 'b.objid=book.id';
			$data =  $this->Boperation->table('__BOPERATION__ b,__BOOK__ book')->where($params)
					->field('b.userid,b.creatime,b.factory,book.id resourceid,book.name')->order('b.creatime desc')->page($page)->select();
		}

		$totolcount = $this->Boperation->where(array('userid'=>$localid,'factory'=>$belongid,'operation'=>$type))->count();
		return array_merge(array('data'=>$data),array('totalcount'=>$totolcount));
	}


}
