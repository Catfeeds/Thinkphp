<?php
namespace Api\Controller;
use Think\Controller;
class AdsController extends Controller {
    public function __construct()
    {

        $this->appLogic = D('App', 'Logic');
        $this->videoLogic = D('Video', 'Logic');
        $this->bannerLogic = D('Banner', 'Logic');
	    $this->Book = M('Book');
	    $this->Bookparam = M('Bookparam');
    }

    private $appLogic;
    private $videoLogic;
    private $bannerLogic;
	private $Book;
	private $Bookparam;
    /**
     * 获取splash广告
     * @return json : data
     */
    public function splash() {
        $ret = array(
            "img" => "http://api.pinet.co/application/static/img/splash.png",
            "title" => "美菱冰箱"
        );
        $this->ajaxReturn($ret);
    }
    /**
     * 获取banner广告
     * @return json : data
     */
    public function banner() {
        $p = getCurPage();
        $where['end_time']=array('gt',date('Y-m-d H:i:s'));
        $res = $this->bannerLogic->getBannerList($where,$p);
        $this->ajaxReturn($res);
    }

	/**
	 * 获取广告
	 * @param position:广告位置 1.顶部广告 2.中部广告 3.底部广告 4.开屏广告
	 * @platform int :1 Android 2 IOS 默认为1 Android
	 * @return json :data
	 */
	public function getBanner(){
		$position  = I('post.position','','int');
		if(!in_array($position,array(1,2,3,4))){
			$position = 9;
		}
		$type =  I('post.platform','1','int');
		if(!in_array($type,array(1,2))){
			$type = 1;
		}
		$data = $this->bannerLogic->getBanner($position,$type);
		$this->ajaxReturn($data);
	}

    /**
     * 幻灯片信息获取接口
     * @param int type : （可选）类型：0:视频 1:游戏（默认）  2:应用 3：图书
     * @return json : data
     */
    public function getappimgs()
    {
	    $type = I('post.type',null,'int');
	    $mycond['slide'] = '1';
	    $mycond['status'] = '1';
	    $mycond['_string'] = 'isdel is null';
	    if(!in_array($type,array('0','2','3')) ){
		    $mycond['apptype'] = C('GAMES_CATEGORY');
	    }else if($type == 2){
		    $mycond['apptype'] = C('APP_CATEGORY');
	    }else if($type == 3){
			$data = $this->Book->where($mycond)->field('id,name,cover imgurl')->order(C('RECOMMEND_DEFAULT_SORT').' desc')->select();
		    foreach ($data as $key=>$value) {
			    $bookurl = $this->Bookparam->field('chapteraddress')
					    ->where('bookid='.$value['id'])->order('sort')->find();
			    $data[$key]['bookurl'] = $bookurl['chapteraddress'];
		    }

		    $this->ajaxReturn($data);
	    }else{
		    $mycond['apptype'] = '1';
	    }
        $data = $this->appLogic->getAppList($mycond);
        $this->ajaxReturn($data);
    }

    /**
     * 视频图片信息获取接口
     * @param int id : 必填
     * @return json : data
     */
    public function getimg(){
        $uuid = I('post.id');
        $data = $this->videoLogic->getimgById($uuid);
        foreach($data as $v){
            $cover['img'] =  C('MMS_SERVER').'/'.$uuid.'/'.$v['cover'];
        }
        $this->ajaxReturn($cover);
    }

	/**
	 * 第三方直播地址获取
	 */
	public function GetLiveVideo(){
		$url = 'http://m.kktv5.com/?cid=8049';
		$this->ajaxReturn(array('url'=>$url));
	}

}