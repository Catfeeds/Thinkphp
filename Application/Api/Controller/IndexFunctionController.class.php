<?php
namespace Api\Controller;
use Think\Controller;
class IndexFunctionController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->IndexFunction = D('IndexFunction', 'Logic');
        $this->Function = M('Function');
	    $this->PlifeApp = M('Plifeapp');
    }

    private $IndexFunction;
    private $Function;
	private $PlifeApp;

    public function index(){}
    /**
     * 首页功能文字描述查询接口
     * @return json : data
     */
    public function GetModelDesc()
    {
        $data = $this->IndexFunction->getFunctionList();
        $this->ajaxReturn($data);
    }

	/*
	 *一键上网接口
	 */
	public function linkWiFi(){
		$url = C('WIFI_URL');
		$this->ajaxReturn(array('url'=>$url));
	}

	/**
	 * 商城入口
	 */
	public function getMall(){
		$url = C('MALL_URL');
		$this->ajaxReturn(array('url'=>$url));
	}


	/**
	 * DNS接口服务
	 * @return json : data
	 */
	public function getDNSAddress(){
		$data['mss'] = WEB_ROOT;
		$this->ajaxReturn($data);
	}

	/**
	 * 版本检查接口
	 * 参数：
	 * @param appversion sting : 客户派生活app版本号
	 * @param type int: 产品类型 1：Android 2:IOS 默认为1：Android
	 *
	 * @return json : data
	 */
	public function checkVersion(){
		$data = array();
		$appversion = trim(I('post.appversion','','string'));
		if(empty($appversion)){
			$this->ajaxReturn(array('ret'=>'-3','msg'=>'传入数据有误'));
		}
		$type =  I('post.type','1','int');
		if(!in_array($type,array(1,2))){
			$type = 1;
		}
		if($type == 1){
			$result = $this->PlifeApp->where('isdel is null and status=1 ')->find();
			//if($appversion < $result['version'] ){
			if(version_compare($appversion,$result['version'],'lt')){
				if(C('APP_UPDATE') == 0){
					$data['rst'] = '-1';
					$data['msg'] = '发现新版本，点击更新';
				}else{
					$data['rst'] = '-2';
					$data['msg'] = '此版本太低，请更新后使用 ';
				}
				$data['url'] = C('RESOURCE_URL').$result['filepath'];

			}else{
				$data['rst'] = '0';
				$data['msg'] = 'Can enter the application';
			}
			$this->ajaxReturn($data);
		}else{
			$this->ajaxReturn(array('rst'=>0,'msg'=>'测试数据'));
		}

	}

	/**
	 * 系统消息获取
	 * 参数：
	 * @param int userid : 用户ID
	 * @param int pages :当前分页(默认为1)
	 * @param int rowcount :每页返回记录数（默认为系统设置值）
	 * @retrun json : data
	 */
	public function getNotices(){
		$params = array();
		$uid = I('post.userid','','int');
		if(!empty($uid)){
			$params['userid'] = $uid;
		}else{
			$this->ajaxReturn(array('rst'=>'-1','msg'=>'需要用户ID'));
		}

		$pages = I('post.pages','','int')?I('post.pages','','int'):1;
		$rowcount = I('post.rowcount','','int')?I('post.rowcount','','int'):C('MOB_REC_PER_PAGE');

		$data = $this->IndexFunction->getNotices($params,$pages,$rowcount);
		$this->ajaxReturn($data);
	}

	/**
	 * 系统消息检查
	 * @param int userid ： 用户ID
	 *
	 * @return json : data
	 */
	public function checkNotices(){
		$params = array();
		$uid = I('post.userid','','int');
		if(!empty($uid)){
			$params['userid'] = $uid;
		}
		$num = $this->IndexFunction->checkNotices($params,2);//2代表系统消息检查
		$data = array();
		if($num > 0){
			$data['rst'] = '0';
			$data['msg'] = 'success';
			$data['num'] = $num;
		}else{
			$data['rst'] = '-1';
			$data['msg'] = 'No new messages ';
		}
		$this->ajaxReturn($data);
	}

	/**
	 * 意见收集接口
	 * @param string contactinfo : 联系方式
	 * @param string content : 内容
	 *
	 * @return json : data
	 */
	public function collectComments(){
		$params = array();
		$contact = trim(I('post.contactinfo','','string'));
		if(!empty($contact)){
			$params['contact'] =  $contact;
 		}

		$content = trim(I('post.content','','string'));
		if(!empty($content)){
			$params['content'] = $content;
		}

		$data = array();
		$data['rst'] = '-1';
		$data['msg'] = 'failure';
		if(isset($params['contact']) && isset($params['content'])){
			$params['creatime'] = date('Y-m-d H:i:s');
			$rs =  $this->IndexFunction->collectComments($params);
			if($rs){
				$data['rst'] = '0';
				$data['msg'] = 'success';
			}
		}

		$this->ajaxReturn($data);
	}

	/**
	 * 免责申明接口
	 * @param type : 0 免责申明 1 服务协议
	 * @return json:data
	 */
	public function getStatement(){
		$type = I('post.type',null,'int');
		$data = array();
		if(isset($type)){
			switch($type){
				case 0: $params['type'] = '3';break;
				case 1: $params['type'] = '4';break;
				default: $this->ajaxReturn(array('msg','查询错误'));
			}
			$data = $this->IndexFunction->getStatement($params);
		}
		$this->ajaxReturn($data);
	}




}