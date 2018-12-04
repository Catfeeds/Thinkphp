<?php
namespace Api\Controller;
use Think\Controller;
class RecordController extends Controller {
    public function __construct(){
        $this->Res = D('Resource','Logic');
        $this->Video=  M('Video');
        $this->App=  M('App');
		$this->Records = D('Records','Logic');
	    $this->Boperation = M('Boperation');
	    $this->User =  D('User','Logic');
	    $this->Task =  D('Task','Logic');
    }
    private $Res;
    private $App;
    private $Video;
	private $Records;
	private $Boperation;
	private $User;
	private $Task;
    /**
     * 下载计数统计接口
     * 参数：
     * @param int id : （必填）应用id
     */
    public function download(){
        $id = I('post.id','','int');
        if($id) {
            $data = $this->Res->getAppById($id);
            $arr['downtimes'] = $data['downtimes']+1;
            $this->App->where('id='.$id)->save($arr);
        }
        echo  $arr['downtimes']?$arr['downtimes']:0;
    }

    /**
     * 播放计数统计接口
     * 参数：
     * @param int id : （必填）视频id
     */
    public function play(){
        $id = I('post.id','','int');
        if($id) {
            $data = $this->Res->getVideoById($id);
            $arr['viewtimes'] = $data['viewtimes']+1;
            $this->Video->where('id='.$id)->save($arr);
        }
        echo  $arr['viewtimes']?$arr['viewtimes']:0;
    }

    /**
     * 保存用户操作记录
     * 参数：
     * @param string uid : 用户ID
     * @param string operation : 用户操作
     * @param string objid : （可选）操作对象ID
     * @param stirng subobjid : （可选）剧集，章节
     * @param Time videotime : (可选) 视频播放时间
     * @param stirng keywords : 搜索关键字
     * 操作类型：0.下载app 1.视频播放 2.搜索(21:视频搜索 22：APP搜索) 3.阅读图书 4.打开广告 5.查询 6.打开游戏 7.打开应用 8.注册登入 9.上网 10.系统消息阅读
     *//*
    public function OperationRecord(){
		$params = array();

	    //获取用户ID
	    $uid = I('post.uid',null,'int');
	    if(isset($uid)){
			$params['uid'] = $uid;
	    }

	    //操作类型
	    $operation = I('post.operation',null,'int');
	    if(isset($operation)){
		    $params['operation'] = $operation;
	    }

		//操作对象
	    $objid = I('post.objid',null,'int');
	    if(isset($objid)){
		    $params['objid'] = $objid;
	    }

	    //剧集，章节
	    $subobjid = trim(I('post.subobjid'));
	    if(!empty($subobjid)){
		    $params['subobjid'] = $subobjid;
	    }

	    //视频以播放时间
	    $videotime = trim(I('post.videotime'));
	    if(!empty($videotime)){
		    $params['public'] = $videotime;
	    }

	    //搜索关键字
	    $keywords = trim(I('post.keywords'));
	    if(!empty($keywords)){
		    $params['public'] = $keywords;
	    }

        $params['creatime'] = date("Y-m-d H:i:s");

	    $result =  $this->Operation->add($params);

        if($result){
            $data['rst'] = 0;
            $data['msg'] = 'Add info successes';
        }else{
            $data['rst'] = -1;
            $data['msg'] = 'Add info failed';
        }
        $this->ajaxReturn($data);
    }*/


	/**
	 * 查询用户操作记录
	 * @param string userid : 用户id
	 * @param stirng operation :   （可选）用户操作类型：0.下载app 1.视频播放 2.搜索 3.阅读图书 4.打开广告 5.查询 6.打开游戏 7.打开应用 8.注册登入 9.上网 10.系统消息阅读
	 *              1.播放 2.下载 3.阅读 4.搜索
	 * @param string objid : 操作对象id
	 * @param dateform,dateto  后台设置默认时间区域
	 *//*
	public function searchOperation(){
		$params = array();

		//规定查询某个时间段的数据
		$params['creatime'] = array('between',array(C('DATE_FORM'),C('DATE_TO')));

		//获取用户ID
		$uid = trim(I('post.userid'));
		if(!empty($uid)){
			$params['uid'] = $uid;
		}

		//操作类型
		$a = I('post.operation');
		if(isset($a) && array_key_exists('operation',$_POST)){
			$params['operation'] = I('post.operation','','int');
		}

		//操作对象
		$objid = trim(I('post.objid'));
		if(!empty($objid)){
			$params['objid'] = $objid;
		}

		$data = $this->Records->searchOperation($params);
		$this->ajaxReturn($data);
	}*/

	/**
	 * 查询用户操作记录
	 * @param int userid : 用户id
	 * @param int operation : 操作类型 1.播放 2.下载 3.阅读 41.视频搜索 42.APP搜素
	 * @param int objid : 操作对象id
	 * @param int pages : 当前页数（默认为1）
	 * @param int rowcount : 每页返回记录数量（默认为系统设置）
	 * @param dateform,dateto  后台设置默认时间区域
	 */
	public function searchOperation(){
		$params = array();

		//规定查询某个时间段的数据
		$params['b.creatime'] = array('between',array(C('DATE_FORM'),C('DATE_TO')));

		//获取用户ID
		$userid = I('post.userid',null,'int');
		if(!empty($userid)){
			$params['b.userid'] = $userid;
		}

		//操作类型
		$operation = I('post.operation',null,'int');
		if(isset($operation)){
			$params['b.operation'] = I('post.operation','','int');
		}

		//操作对象
		$objid = I('post.objid',null,'int');
		if(!empty($objid)){
			$params['b.objid'] = $objid;
		}

		$params['b.isdel'] =  array('exp','is null');

		$pages = I('post.pages','','int')?I('post.pages','','int'):1;
		$rowcount = I('post.rowcount','','int')?I('post.rowcount','','int'):C('MOB_REC_PER_PAGE');

		$data = $this->Records->searchOperation($params,$pages,$rowcount);
		$this->ajaxReturn($data);
	}


	/**
	 * 关键字查询
	 * @param int operation : 0.所有搜索 1.视频搜索 2.app搜索
	 * @return json : data
	 */
	public function getKeywords(){
		$params = array();
		$operation = I('post.operation','','int')?I('post.operation','','int'):0;
		switch($operation){
			case 0: $params['operation'] = '4';break;//所有
			case 1: $params['operation'] = '41';break;//视频
			case 2: $params['operation'] = '42';break;//APP
		}
		$data = $this->Records->getKeywords($params);
		$this->ajaxReturn($data);
	}

	/**
	 * 保存用户操作记录
	 * 参数：
	 * @param string userid : 用户ID
	 * @param string operation : 用户操作  操作类型 1：播放 2：下载 3：阅读 41：视频搜索 42.App搜索 5.查看系统消息 6.打开广告 7.分享
	 * @param string objid : （可选）操作对象ID
	 * @param stirng subobjid : （可选）剧集，章节
	 * @param Time videotime : (可选) 视频播放时间
	 * @param stirng keywords : 搜索关键字
	 * @parma int  videonum：视频使用字段，总集数
	 * @param string videolength:视频使用字段，总时长
	 * @param int linktype 1:任务 2:广告
	 */
	public function savebOperation(){
		$params = array();

		$data['rst'] = -1;
		$data['msg'] = '参数错误';

		//获取用户ID
		$userid = I('post.userid',null,'int');
		if(isset($userid)){
			$params['userid'] = $userid;
		}else{
			$this->ajaxReturn($data);
		}

		//操作类型
		$operation = I('post.operation',null,'int');
		if(isset($operation)){
			$params['operation'] = $operation;
		}else{
			$this->ajaxReturn($data);
		}

		//操作对象
		$objid = I('post.objid',null,'int');
		if(isset($objid)){
			$params['objid'] = $objid;
		}

		//剧集，章节
		$subobjid = trim(I('post.subobjid'));
		if(!empty($subobjid)){
			$params['subobjid'] = $subobjid;
		}

		//视频以播放时间
		$videotime = trim(I('post.videotime'));
		if(!empty($videotime)){
			$params['public'] = $videotime;
		}

		//搜索关键字
		$keywords = trim(I('post.keywords'));
		if(!empty($keywords)){
			$params['public'] = $keywords;
		}

		//视频总集数
		$videonum  = I('post.videonum',null,'int');
		if(!empty($videonum)){
			$params['videonum'] = $videonum;
		}

		//视频总时长
		$videolength = trim(I('post.videolength'));
		if(!empty($videolength)){
			$params['videolength'] = $videolength;
		}

		//链接类型 1任务 2 广告
		$linktype = I('post.linktype',null,'int');
		if(isset($linktype)){
			$params['linktype'] = $linktype;
		}

		if($params['operation'] ==1 || $params['operation'] == 2){
			if(!isset($params['objid'])){
				$this->ajaxReturn($data);
			}
		}

		$result = $this->Records->savebOperation($params);

		if($result){
			$data['rst'] = 0;
			$data['msg'] = 'Add info successes';
		}else{
			$data['rst'] = -1;
			$data['msg'] = 'Add info failed';
		}

		//如果为下载操作  且为任务跳转
		if($params['operation'] == 2 && $params['linktype'] == 1){
			$checktask =  $this->Task->getIdTask(array('appid'=>$params['objid']));
			//第一次任务保存完成记录的同时加分
			$this->Task->saveUserTask(array('userid'=>$params['userid'],'taskid'=>$checktask['id']));
			$this->User->updateTotalScore($userid,$checktask['score']);
			$data['msg'] = '首次下载该应用，积分+'.$checktask['score'];
		}

		//如果为分享 首次加分
		if(isset($params['operation']) && $params['operation'] == 7){
			$timestart = date('Y-m-01 00:00:00');
			$timeend =  date('Y-m-d 00:00:00',strtotime("$timestart +1 day"));
			$fenxiang['creatime'] =  array('between',"$timestart,$timeend");
			$fenxiang['userid'] = $userid;
			$result = $this->Boperation->where($fenxiang)->count();
			if($result == 0){
				$this->User->updateTotalScore($userid,C('task_socre_fenxiang'));
				$data['msg'] = '首次分享，积分+'.C('task_socre_fenxiang');
			}
		}

		$this->ajaxReturn($data);
	}

	/**
	 * 删除操作记录
	 * @param json arr:记录ID json对象
	 * return json data
	 */
	public function delOperation(){
		$arr = json_decode($_POST['arr']);
		if(count($arr)<1){
			$this->ajaxReturn(array('rst'=>'-1','msg'=>'参数不能为空'));
		}
		$reault = $this->Records->delOperation($arr);
		if($reault){
			$this->ajaxReturn(array('rst'=>'0','msg'=>'删除成功'));
		}else{
			$this->ajaxReturn(array('rst'=>'-1','msg'=>'操作失败，请重新尝试'));
		}
	}


}