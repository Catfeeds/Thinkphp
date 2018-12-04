<?php
namespace Api\Controller;
use CommLib\Hmplay as HmplayClass;
use Think\Controller;

class GamesController extends Controller {

	private $GamesLogic;
	private $categoryLogic;
	private $bannerLogic;
	private $Boperation;

	public function __construct(){
		$this->GamesLogic = D('Games','Logic');
		$this->categoryLogic = D('Category','Logic');
		$this->bannerLogic = D('Banner','Logic');
		$this->Boperation = M('Boperation');
	}

	/**
	 * 检测应用是否有更新
	 * 参数：
	 * @param array arr: 数组格式的参数
	 * @param string pkg:包名
	 * @param string version:版本
	 * @return json
	 */
	public function checkVersion(){
		$arr = json_decode($_POST['arr']);
		if(count($arr)<1){
			$this->ajaxReturn(array('rst'=>'-1','msg'=>'参数不能为空'));
		}
		$result = $this->GamesLogic->checkVersion($arr);
		if($result){
			foreach($arr as $key=>$value){
				foreach($result as $key1=>$value1){
					//if($arr[$key]->version < $value1['version'] && $arr[$key]->pkg == $value1['pkg']){
					if(version_compare($arr[$key]->version,$value1['version'],'lt') && $arr[$key]->pkg == $value1['pkg']){
						$data[] = $result[$key1];
					}
				}
			}
			if(count($data)>0){
				$this->ajaxReturn($data);
			}else{
				$this->ajaxReturn(array('rst'=>'0','msg'=>'没有查找到更新'));
			}
		}else{
			$this->ajaxReturn(array('rst'=>'0','msg'=>'没有查找到更新'));
		}
	}


	/**
	 * 分页获取全部游戏列表（含搜索）
	 * 参数：
	 * @param int pages :（可选）分页，默认为1
	 * @param string keywords: 搜索关键词，如无，则给所有列表（按分页），暂时只可以用一个关键词，后期增加多关键词搜索
	 * @param int apptype :（可选）类型：1:游戏（默认）  2:应用  ** 分类id 默认游戏 **
	 * @param int fitertype :（可选）类型：1:最新（默认）  2:最热
	 * @param string appcategroy : (可选) 分类：‘益智休闲’，‘动作冒险’..，默认：全部 ** 二级分类id 如休闲益智的ID **
	 * @param int rowcount （可选）每页返回记录数
	 * @param int promotenumber 为推荐时必含参数，推荐视频个数
	 * @param int id:资源id
	 * @return json : list
	 * */
	public function games(){
		$params = array();
		$apptype = I('post.apptype',null,'int')?I('post.apptype',null,'int'):C('GAMES_CATEGORY');
		$params['apptype'] = $apptype;

		//游戏搜索
		$search = trim(I('post.keywords'));
		if(!empty($search)){
			$params['name'] =array('like', array("%$search%"),'OR');
		}

		//是否是推荐
		$recommend = trim(I('post.promotenumber'));
		if(!empty($recommend)){
			$params['isrecommend'] = 1;
		}
		

		//游戏最新或者最热查询
		$sort= trim(I('post.fitertype'));
		if(!in_array($sort, array('1', '2'))){
			$sort = '1';
		}

		//按照分类筛选
		$tagsid = I('post.appcategroy',null,'int');
		if(isset($tagsid)){
			$params['tagsid'] = $tagsid;
		}

		//按照id查询
		$id = trim(I('post.id',null,'int'));
		if(!empty($id)){
			$params['id'] =  $id;
		}

		$page = I('post.pages','','int') ? I('post.pages','','int') : 1;

		$games = null;
		$promotenumber = I('post.promotenumber','','int') ? I('post.promotenumber','','int') : 0;
		$rowcount = I('post.rowcount','','int') ? I('post.rowcount','','int') : 0;

		//是否为推荐查询
		if($promotenumber){
			//参数是否包含每页返回记录
			if($rowcount){
				$games = $this->GamesLogic->getRecomGames($params,$promotenumber,$page,$rowcount);
			}else{
				$games = $this->GamesLogic->getRecomGames($params,$promotenumber,$page);
			}
		}else{
			//参数是否包含每页返回记录数
			if($rowcount){
				$games = $this->GamesLogic->getGames($params, $page, $sort,$rowcount);
			}else{
				$games = $this->GamesLogic->getGames($params, $page, $sort);
			}
		}

		$this->ajaxReturn($games);
	}

	/**
	 * 获取最高人气游戏
	 * 参数：
	 * @param number int (可选)返回记录数量
	 * @param pages int 分页数，默认为1
	 * @param rowcount int （可选）每页返回记录数
	 * @param type int 1:游戏（默认） 2：应用  ** APP类型ID 默认游戏70**
	 */
	function getPopApps(){
		$page = I('post.pages','','int') ? I('post.pages','','int') : 1;
		$number = I('post.number','','int') ? I('post.number','','int') : C('POP_NUM');
		$rowcount = I('post.rowcount','','int') ? I('post.rowcount','','int') : 0;

		$type = I('post.type','','int')?I('post.type','','int'):70;
		$games = $this->GamesLogic->getPopApps($page,$number,$rowcount,$type);

		$this->ajaxReturn($games);
	}


	/**
	 * 上月精品游戏推荐
	 * 参数：
	 * @param number int (可选)返回记录数量
	 * @param pages int 分页数，默认为1
	 * @param rowcount int (可选) 每页返回记录数
	 */
	/*
	function getMonthPopApps(){
		$page = I('post.pages','','int') ? I('post.pages','','int') : 1;
		$number = I('post.number','','int') ? I('post.number','','int') : C('POP_NUM');
		$rowcount = I('post.rowcount','','int') ? I('post.rowcount','','int') : 0;

		$games = $this->GamesLogic->getMonthPopApps($page,$number,$rowcount);

		$this->ajaxReturn($games);
	}*/

	/**
	 * 获取榜单
	 * @param number int (可选)返回记录数量
	 * @param pages int 分页数，默认为1
	 * @param rowcount int (可选) 每页返回记录数
	 * @param statistictype int 类型： 1.月榜（默认） 2.周榜
	 * @param type int 1.游戏(默认) 2.应用  ** 类型ID 默认游戏70 **
	 * @return json data
	 */
	function downloadStatistic(){
		$page = I('post.pages','','int')?I('post.pages','','int'):1;
		$number = I('post.number','','int')?I('post.number','','int'):C('POP_NUM');
		$rowcount = I('post.rowcount','','int') ? I('post.rowcount','','int') : 0;

		//获取种类，默认为月榜
		$statistictype = I('post.statistictype','','int');
		if(!in_array($statistictype,array('1','2'))){
			$statistictype = 1;
		}

		//获取类型，默认为游戏
		$type = I('post.type','','int')?I('post.type','','int'):70;

		$data = $this->GamesLogic->downloadStatistic($page,$number,$rowcount,$type,$statistictype);

		$this->ajaxReturn($data);
	}

	/**
	 * 游戏评论保存
	 * @param int objid : AppID
	 * @param int userid : 用户ID
	 * @param int score : 评分
	 * @param stirng comment : 评论
	 *
	 * @return json : data
	 */
	public function saveAppComment(){
		$params = array();//保存参数
		$data = array();//保存返回数据

		//获取资源ID
		$objid = I('post.objid',null,'int');
		if(isset($objid)){
			$params['objid'] = $objid;
		}
		//用户ID
		$userid  = I('post.userid',null,'int');
		if(isset($userid)){
			$params['userid'] = $userid;
		}
		//评分
		$score = I('post.score',null,'float');
		if(isset($score)){
			$params['score'] =  $score;
		}
		//评论内容
		//$content = trim(I('post.comment',null,'string'));
		$contentaal = explode('&',file_get_contents('php://input'));
		$content =  urldecode(substr($contentaal[0],strpos($contentaal[0],'=')+1));
		//$text = preg_replace_callback('/[\xf0-\xf7].{3}/', function($r) { return '';}, $content);
		$text = preg_replace_callback('/[\xf0-\xf7].{3}/', function($r) { return '@E' . base64_encode($r[0]);}, $content);
		if(!empty($text)){
			$params['content'] = $text;
		}
		//评论时间
		$params['creatime'] = date('Y-m-d H:i:s');

		if(count($params)<5){
			$data['rst'] = '-1';
			$data['msg'] = '请输入完整数据';
		}else{
			$rl = $this->Boperation->where(array('userid'=>$userid,'objid'=>$objid,'operation'=>2))->find();
			if($rl){
				$params['type'] = '2';//代表保存的是应用评论
				$this->GamesLogic->saveAppComment($params);
				$data['rst'] = '0';
				$data['msg'] = '评论成功';
			}else{
				$data['rst'] = '-1';
				$data['msg'] = '您必须使用过才能评论';
			}

		}

		$this->ajaxReturn($data);

	}

	/**
	 * 游戏评论获取
	 * @param int objid : 资源ID
	 * @param int pages:(可选)分页 默认为1
	 * @param int rowcount:(可选)每页返回记录数
	 * @return json : data
	 */
	function getAppComments(){
		$objid = I('post.objid','','int');

		$pages = I('post.pages','','int')?I('post.pages','','int'):1;
		$rowcount = I('post.rowcount','','int')?I('post.rowcount','','int'):C('MOB_REC_PER_PAGE');
		$data = $this->GamesLogic->getAppComments($objid,$pages,$rowcount);
		$this->ajaxReturn($data);
	}

	/**
	 * APP相关推荐
	 * @param int type 1:游戏(默认) 2：APP  ** 应用类型ID **
	 * @param string category : 类型：益智休闲等。。。
	 * @return json data
	 */
	/*
	function relateRecommend(){
		$apptype = I('post.type',null,'int')?I('post.type',null,'int'):70;//默认为游戏
		$tagsid = I('post.category',null,'int');
		if(isset($tagsid)){
			$params['tagsid'] = $tagsid;
		}
		$params['apptype'] =  $apptype;
		$data =  $this->GamesLogic->relateRecommend($params);
		$this->ajaxReturn($data);
	}*/

	/**
	 * 应用相关
	 * @param int id:当前资源的ID
	 * @param json data
	 */
	function relateRecommend(){
		$id = I('post.id','','int');
		$data =  $this->GamesLogic->relateRecommend($id);

		$this->ajaxReturn($data);
	}

	/**
	 * 随机应用
	 * @return json data
	 */
	function randomApps(){
		$apptype = I('post.type',null,'int')?I('post.type',null,'int'):70;//默认为游戏
		$data = $this->GamesLogic->randomApps($apptype);
		$this->ajaxReturn($data);
	}


	function category(){
		$data = $this->categoryLogic->getCategoryList('games');
		$this->ajaxReturn($data);
	}

	function banner(){
		$num = I('post.num','','int') ? I('post.num','','int') : 5;
		$banner = $this->bannerLogic->getBanners('games', $num);
		$this->ajaxReturn($banner);
	}

	function download(){
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
			$params['type'] = 'download';
			$params['create_date'] = date('Y-m-d H:i:s'); 
			$this->recordsLogic->addRecords($params);
			$download_count = $this->GamesLogic->changeDownloadCount($id);
			$message = array('status' => '1', 'message' => $download_count);
		}
		$this->ajaxReturn($message);
	}

	function igames(){
		$hm_config = C('HM_PLAY');	
		$hmplay = new HmplayClass($hm_config['url'], $hm_config['caller'], $hm_config['signKey']);

		$date_from = '20150310092546'; //date('YmdHis');
		$date_to = '20160310092546'; //date('YmdHis');

		$params['sequence'] = 1457539200;
		$params['client'] = array('caller' => $hm_config['caller'], 'ex' => null);
		$params['data'] = array('sync_type' => 1, 'sync_entity' => 'game', 'date_from' => $date_from, 
					'date_to' => $date_to, 'page_size' => 5, 'page_num' => 1);
		$params['sign'] = $hmplay->sign($params['data']);
		$params['encrypt'] = $hm_config['encrypt'];
		$json = json_decode($hmplay->http_request(json_encode($params)));
		if(isset($json->state->code) && $json->state->code == '200'){
			$data = json_decode(base64_decode($json->data));
			$games = $data->list;
			foreach($games as $key => $value){
				$this->syncGames($value);
			}
		}
	}
	
	function syncGames($games = array()){
		if(empty($games)){ exit('No games to sysnc!');}	
		$gamesModel = M('games');
		$imagesModel = M('game_image');
		$fileModel = M('game_file');
		try{
			$gamesModel->startTrans();

			$game['game_id'] = $games->game_id;
			$game['cp_name'] = $games->cp_name;
			$game['game_name'] = $games->game_name;
			$game['game_tag'] = $games->game_tag;
			$game['game_class'] = $games->game_class;
			$game['create_time'] = $games->create_time;
			$game['modify_time'] = $games->modify_time;
			$game['online_status'] = $games->online_status;
			$game['channel_id'] = $games->channel_id;
			$game['introduction'] = $games->introduction;

			if($games->game_images){
				$this->syncGameImages($imagesModel, $games->game_images);
			}
			if($games->game_file){
				$this->syncGameFile($fileModel, $games->game_file);
			}

			$game_rs = $gamesModel->add($game);
			if(!$game_rs){
				throw new \Exception('save game failed!');
			}
			$gamesModel->commit();
		}catch(\Exception $e){
			$gamesModel->rollback();
			exit($e->getMessage());	
		
		}
	}

	function syncGameImages($imagesModel, $images = array()){
		$data = array();
		foreach($images as $key => $image){
			$data[$key]['image_id'] = $image->image_id;
			$data[$key]['game_id'] = $image->game_id;
			$data[$key]['image_type'] = $image->image_type;
			$data[$key]['image_index'] = $image->image_index;
			$data[$key]['image_size'] = $image->image_size;
			$data[$key]['image_height'] = $image->image_height;
			$data[$key]['image_width'] = $image->image_width;
			$data[$key]['image_url'] = $image->image_url;
		}
		$image_rs = $imagesModel->addAll($data);
		if(!$image_rs){
			throw new \Exception('save images failed!');
		}
	}

	function syncGameFile($fileModel, $file = array()){
		$data['file_id'] = $file->file_id;
		$data['file_url'] = $file->file_url;
		$data['game_id'] = $file->game_id;
		$data['file_size'] = $file->file_size;
		$data['channel_id'] = $file->channel_id;
		$data['package_name'] = $file->package_name;
		$data['version_name'] = $file->version_name;
		$data['version_code'] = $file->version_code;
		$data['update_time'] = $file->update_time;
		$file_rs = $fileModel->add($data);
		if(!$file_rs){
			throw new \Exception('save file failed!');
		}
	}



}
