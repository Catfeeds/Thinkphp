<?php
namespace Api\Controller;
use Think\Controller;

class VideoController extends Controller {

	private $videoLogic;
	private $categoryLogic;
	private $bannerLogic;

	public function __construct(){
		$this->videoLogic = D('Video','Logic');
		$this->categoryLogic = D('Category','Logic');
		$this->bannerLogic = D('Banner','Logic');
	}

	/**
	 * 获取游戏直播的所有游戏分类
	 * param int pages :(可选)分页，默认为1
	 * param int rowcount :(可选)每页返回记录数，默认为系统设置值
	 * return json data
	 */
	public function gameLiveType(){
		$pages =  I('post.pages','','int')?I('post.pages','','int'):1;
		$rowcount = I('post.rowcount','','int')?I('post.rowcount','','int'):C('MOB_REC_PER_PAGE');
		if($rowcount<0 || $rowcount >C('MOB_REC_PER_PAGE')){
			$rowcount =  C('MOB_REC_PER_PAGE');
		}
		$url = 'http://open.douyucdn.cn/api/RoomApi/game';
		$result =  json_decode(file_get_contents($url),true);
		if($result['error'] === 0 ){
			$datas = $result['data'];
			$totalcount = count($datas);
			$data = array_slice($datas,($pages-1)*$rowcount,$rowcount,true);
			$this->ajaxReturn(array_merge(array('totalcount'=>$totalcount),array('list'=>$data)));
		}else{
			$this->ajaxReturn(array('rst'=>'-1','msg'=>'网络异常，请稍后再试'));
		}
	}

	/**
	 * 获取游戏直播房间详情信息
	 * @param roomid :房间ID
	 * return json data
	 */
	public function gameLiveRoom(){
		$roomid = I('post.roomid',null,'int');
		if(!isset($roomid)){
			$this->ajaxReturn(array('rst'=>'-1','msg'=>'必须传递房间ID'));
		}
		$url = "http://open.douyucdn.cn/api/RoomApi/room/".$roomid;
		$result =  json_decode(file_get_contents($url),true);
		if($result['error'] === 0){
			$data = $result['data'];
			$this->ajaxReturn($data);
		}else{
			$this->ajaxReturn(array('rst'=>'-1','msg'=>'没有找到该房间'));
		}
	}

	/**
	 * 获取直播房间列表
	 * @param cataid int :房间分类ID,默认所有类型房间
	 * @param pages int :当前页数，默认为1
	 * @param rowcount  int :每页返回记录数，默认为系统设置
	 * return json data
	 */
	public function gameLiveList(){
		$cataid = I('post.cataid',null,'int');
		$pages =  I('post.pages','','int')?I('post.pages','','int'):1;
		$rowcount = I('post.rowcount','','int')?I('post.rowcount','','int'):C('MOB_REC_PER_PAGE');
		if($rowcount<0 || $rowcount >C('MOB_REC_PER_PAGE')){
			$rowcount =  C('MOB_REC_PER_PAGE');
		}
		$offset = ($pages-1)*$rowcount;
		if(isset($cataid)){
			$url = "http://open.douyucdn.cn/api/RoomApi/live/$cataid?offset=$offset&limit=$rowcount";
		}else{
			$url = "http://open.douyucdn.cn/api/RoomApi/live?offset=$offset&limit=$rowcount";
		}
		$result =  json_decode(file_get_contents($url),true);
		if($result['error'] === 0){
			$data = $result['data'];
			$this->ajaxReturn($data);
		}else{
			$this->ajaxReturn(array('rst'=>'-1','msg'=>'获取失败，请重新尝试'));
		}
	}


	/**
	 * 分页获取全部视频列表（含搜索）
	 * 参数：
	 * @param int pages :（可选）分页，默认为1
	 * @param string keywords: 搜索关键词，如无，则给所有列表（按分页），暂时只可以用一个关键词，后期增加多关键词搜索
	 * @param int videotype :（可选）类型：1:电影（默认）  2:电视剧 0:所有  ** 视频类型 默认所有**
	 * @param int filtertype :（可选）类型：1:最新（默认）  2:最热
	 * @param string videocategory : (可选) 口味：‘剧情’，‘动作’，...，默认：全部  **对应id  默认全部**
	 * @param string area : (可选) 国家：‘中国’，‘美国’，...，默认：全部   **国家id 默认全部 **
	 * @param int year : （可选）年份：‘2016’，‘2015’，...，默认：全部
	 * @param int rowcount （可选）每页返回记录数
	 * @param int promotenumber 为推荐时必含参数，推荐视频个数
	 * @oaran int id:资源ID
	 *
	 * @return json : list
	 * */
	public function videos(){
		$params = array();
		/*
		if(I('post.videotype','','int') == 1 || I('post.videotype','','int') == 2 ||I('post.videotype','','int') == 0){$type = I('post.videotype','','int');}else{ $type=1; }
		$params['type'] = $type;
		if($params['type'] == 0){
			unset($params['type']);
		}*/
		$videotype = I('post.videotype',null,'int');
		if(isset($videotype)){
			$params['videotype'] = $videotype;
		}

		//视频搜索
		$search = trim(I('post.keywords'));
		if(!empty($search)){
			$paramss['name'] =array('like', array("%$search%"));
			$paramss['director'] =array('like', array("%$search%"));
			$paramss['actors'] =array('like', array("%$search%"));
			$paramss['_logic'] = 'or';
			$params['_complex'] = $paramss;
		}

		//是否是推荐
		$recommend = trim(I('post.promotenumber'));
		if(!empty($recommend)){
			$params['isrecommend'] = 1;
		}

		//视频最新或者最热查询
		$sort= trim(I('post.filtertype'));
		if(!in_array($sort, array('1', '2'))){
			$sort = '1';
		}

		
		//按照分类筛选
		$categoryid = I('post.videocategory',null,'int');
		if(isset($categoryid)){
			$params['categoryid'] = $categoryid;
		}

		//按照区域筛选
		$countrycid = I('post.area',null,'int');
		if(isset($countrycid)){
			$params['countrycid'] =  $countrycid;
		}

		//按照年筛选
		$min_five_year = date("Y")-5;
		$year = I('post.year',null,'int');
		if(isset($year)){
			$params['years'] = $year;
		}
		if(isset($year) && $year <= $min_five_year) {
			$params['years'] = array('elt',$min_five_year);
		}

		$params['_string'] = "isdel is null";
		//按照uuid查询
		$uuid = trim(I('post.id'));
		if(!empty($uuid)){
			$params['id'] =  $uuid;
		}

		$page = I('post.pages','','int') ? I('post.pages','','int') : 1;

		$vlist = null;
		$promotenumber = I('post.promotenumber','','int') ? I('post.promotenumber','','int') : 0;
		$rowcount = I('post.rowcount','','int') ? I('post.rowcount','','int') : 0;
		//是否为推荐查询
		if($promotenumber){
			//参数是否包含每页返回记录
			if($rowcount){
				$vlist = $this->videoLogic->getRecomVideos($params,$promotenumber,$page,$rowcount);
			}else{
				$vlist = $this->videoLogic->getRecomVideos($params,$promotenumber,$page);
			}
		}else{
			//规定查询某个时间段的数据
			$params['creatime'] = array('between',array(C('DATE_FORM'),C('DATE_TO')));

			//参数是否包含每页返回记录数
			if($rowcount){
				$vlist = $this->videoLogic->getVideos($params, $page, $sort,$rowcount);
			}else{
				$vlist = $this->videoLogic->getVideos($params, $page, $sort);
			}
		}

		$this->ajaxReturn($vlist);
	}

	/**
	 * 视频相关推荐
	 * @param int type 1:电影（默认） 2：电视剧  **视频类型id 默认电影 **
	 * @param string category : 类型：运动、冒险等。。。 **类型id **
	 * @param int id :当前资源的ID
	 * @return json data
	 */
	/*
	function relateRecommend(){
		$videotype = I('post.type',null,'int')?I('post.type',null,'int'):65;//默认为电影video
		$params['videotype'] = $videotype;
		//$tags = trim(I('post.category'));
		$tags = I('post.category',null,'int');
		if(isset($tags)){
			$params['categoryid'] = $tags;
		}

		$data =  $this->videoLogic->relateRecommend($params);
		$this->ajaxReturn($data);
	}*/
	/**
	 * 视频相关推荐
	 * @param int id:当前资源的ID
	 * @param json data
	 */
	function relateRecommend(){
		$id = I('post.id','','int');
		$data =  $this->videoLogic->relateRecommend($id);

		$this->ajaxReturn($data);
	}

	function columns(){
		$category = array(
			array('type' => '1', 'name' => '电影', 'icon' => ''),
			array('type' => '2', 'name' => '电视剧', 'icon' => ''),
		);
		$this->ajaxReturn($category);
	}

	function category(){
		$data = $this->categoryLogic->getCategoryList('video');
		$this->ajaxReturn($data);
	}

	function areas(){
		$data = $this->categoryLogic->getCategoryList('area');
		$this->ajaxReturn($data);
	}

	function years(){
		$cur_year = date('Y');
		$years = array();
		for($i=0; $i<5; $i++){
			$years[$cur_year - $i] = $cur_year - $i;
			if($i == 4) $years[$cur_year - $i] = '更早';
		}	
		$this->ajaxReturn($years);
	}

	function movieBanner(){
		$num = I('post.num','','int') ? I('post.num','','int') : 5;
		$banner = $this->bannerLogic->getBanners(array('category' => 'movie'), $num);
		$this->ajaxReturn($banner);
	}

	function dramaBanner(){
		$num = I('post.num','','int') ? I('post.num','','int') : 5;
		$banner = $this->bannerLogic->getBanners(array('category' => 'drama'), $num);
		$this->ajaxReturn($banner);
	}

	function play(){
		$id = I('post.id');	
		$message = array();
		if(empty($id)){
			$message = array('status' => '0', 'message' => 'no parameter id');
		}else{
			$params['mac'] = I('post.mac') ? I('post.mac') : ''; 
			$params['ip'] = I('post.ip') ? I('post.ip') : ''; 
			$params['os'] = I('post.os') ? I('post.os') : ''; 
			$params['os_version'] = I('post.os_version') ? I('post.os_version') : ''; 
			$params['third_id'] = $id; 
			$params['type'] = 'play'; 
			$params['create_date'] = date('Y-m-d H:i:s'); 
			$this->recordsLogic->addRecords($params);
			$play_count = $this->videoLogic->changePlayCount($id);
			$message = array('status' => '1', 'message' => $play_count);
		}
		$this->ajaxReturn($message);
	}


}
