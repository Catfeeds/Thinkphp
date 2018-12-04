<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 16/05/08
 * Time: 上午9:02
 */

namespace Api\Logic;


class LogLogic extends \Think\Model{
    public function __construct(){
        $this->Log = M('Usedata');
    }
    private $Log;

    /**
     * @param $resid  资源id
     * @param $restype 资源类型：1:视频 2:app 3:book
     * @param $localuid 用户id
     * @param $facid  厂区id
     * @param string $time 时间
     * @return bool
     */
    public function addUseLog($resid,$restype,$localuid,$facid,$time=''){
		$ndata = array();
        $ndata['resid'] = (int)$resid;
        $ndata['restype'] = (int)$restype;
        $ndata['localuid'] = (int)$localuid;
        $ndata['facid'] = (int)$facid;
        if($time!=''){$ndata['ctime'] = $time;}
        $this->Log->add($ndata);
        return true;
    }

}