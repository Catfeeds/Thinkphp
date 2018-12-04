<?php

/* reated by vim.
 * User: Jason Hu
 * Date: 2016-03-07
 * Time: 18:50
 * */

namespace Api\Logic;


class GamesLogic extends \Think\Model{

	private $gamesModel;
	//private $operationModel;
	private $Comment;
	private $appOrder = array(1=>"creatime", 2=>"downtimes");   // 排序关键词，1:时间，2:热门
	private $Model;
	private $Category;

	public function __construct(){
		$this->gamesModel = M('app');
		//$this->operationModel = M('operation');
		$this->Comment = M('Comment');
		$this->Model = M();
		$this->Category = M('Category');
	}

	/**
	 * 检测应用是否有更新
	 * 参数：
	 * @param array arr: 数组格式的参数
	 *      @param string pkg:包名
	 *      @param string version:版本
	 * @return json
	 */
	public function checkVersion($arr){
		//$pkg = array_column($arr,'pkg');
		foreach($arr as $key=>$value){
			$pkg[] = $arr[$key]->pkg;
		}
		$params['isdel'] = array('exp','is null');
		$params['status'] = '1';
		$params['pkg'] = array('in',$pkg);
		$result =  $this->gamesModel->where($params)->select();
		return $result;
	}

	/**
 * 分页带获取游戏列表
 * 参数：
 * @param array $condition : （可选）搜索条件
 * @param int $page : （可选）分页，默认为1 （不需要额外定义列表的数量）
 * @param int $sort: （可选）排序依据，默认为 1:最新，2: 最热
 * @param int $limit : （可选）每次获取的记录条数，默认系统分配（也不可超过系统分配限制数）
 * @return json : list
 **/
	public function getGames($condition=array(), $page=1, $sort=1, $limit=0){
		$mycond = array();
		if(is_array($condition) && count($condition)>0){
			$mycond = $condition;
		}
		$mycond['status'] = '1';

		if($limit>0 && $limit<C('MOB_REC_PER_PAGE')){
			$curpage = $page.','.$limit;
		}else{
			$curpage = $page.','.C('MOB_REC_PER_PAGE');
		}
		if (array_key_exists($sort, $this->appOrder)){
			$order = $this->appOrder[$sort];}
		else{
			$order = $this->appOrder[1];
		}
		$data = $this->gamesModel->where($mycond)->page($curpage)->order("$order desc")->select();
		$data = array('list'=>$data);
		$totalcount = array('totalcount'=>$this->gamesModel->where($mycond)->count());
		$category = array('category'=>$this->gamesModel->where($mycond)->field('tags category,count(tags) num')->group('tags')->order('num desc')->select());

		$apptype = $this->gamesModel->where($mycond)->field('apptype,count(apptype) num')->group('apptype')->order('num desc')->select();
		foreach($apptype as $key=>$value){
			$cate =  $this->Category->where('pid=2')->field('id,name')->select();
			foreach($cate as $key1=>$value1){
				if($value['apptype'] == $value1['id']){
					$apptype[$key]['apptypename'] =$value1['name'];
				}
			}
		}

		$apptype = array('apptype'=>$apptype);
		$data = array_merge($totalcount,$data,$category,$apptype);
		/*
		if(isset($mycond['id'])){
			$average1 =  $this->Comment->where('objid='.$mycond['id'].'  and audit=1 and type in ('.C('GAMES_CATEGORY').','.C('APP_CATEGORY').')')->avg('score');
			$average = array('average'=>$average1);
			$data =  array_merge($totalcount,$data,$category,$apptype,$average);
		}else{
			$data = array_merge($totalcount,$data,$category,$apptype);
		}*/
		return $data;
	}

	/**
	 * 分页带获取推荐游戏列表
	 * 参数：
	 * @param array $condition : （可选）搜索条件
	 * @param int $page : （可选）分页，默认为1 （不需要额外定义列表的数量）
	 * @param int $limit : （可选）每次获取的记录条数，默认系统分配（也不可超过系统分配限制数）
	 * @param int $promotenumber (必含)推荐视频个数
	 * @return json : list
	 **/
	public function getRecomGames($condition=array(),$promotenumber,$page=1,$limit=0){
		$mycond = array();
		if(is_array($condition) && count($condition)>0){
			$mycond = $condition;
		}
		$mycond['status'] = '1';
		$mycond['DATEDIFF(now(),creatime)'] = array('elt',C('TIME_BUCKET')*30);

		if($limit>0 && $limit<C('MOB_REC_PER_PAGE')){
			$curpage = $page.','.$limit;
		}else{
			$limit = C('MOB_REC_PER_PAGE');
			$curpage = $page.','.C('MOB_REC_PER_PAGE');
		}

		if($promotenumber<=$limit){
			//返回数据小于每页数据数量
			$data =  $this->gamesModel->where($mycond)->limit(0,$promotenumber)->order(C('RECOMMEND_DEFAULT_SORT')." desc")->select();
		}else if($promotenumber%$limit  == 0){
			$data =  $this->gamesModel->where($mycond)->page($curpage)->order(C('RECOMMEND_DEFAULT_SORT')." desc")->select();
		}else{
			$nowpage = ceil($promotenumber/$limit);
			if($page == $nowpage){
				$data = $this->gamesModel->where($mycond)->limit(($page-1)*$limit,$promotenumber%$limit)->order(C('RECOMMEND_DEFAULT_SORT')." desc")->select();
			}else{
				$data = $this->gamesModel->where($mycond)->page($curpage)->order(C('RECOMMEND_DEFAULT_SORT')." desc")->select();
			}
		}
		$totalcount = array('totalcount'=>$this->gamesModel->where($mycond)->count());//获取总数
		$data = array('list'=>$data);
		$data = array_merge($totalcount,$data);//组成新的数据

		return $data;

	}

	/**
	 * 最高人气查询
	 * 参数：
	 * @param page int 页数
	 * @param limit int 每页返回记录数
	 * @param number 返回记录数量
	 * @return mixed
	 */
	function getPopApps($page,$number,$limit,$type){
		$mycond['status'] = '1';
		$mycond['apptype'] = $type;

		if($limit>0 && $limit<C('MOB_REC_PER_PAGE')){
			$curpage = $page.','.$limit;
		}else{
			$limit = C('MOB_REC_PER_PAGE');
			$curpage = $page.','.C('MOB_REC_PER_PAGE');
		}

		if($number<$limit){
			$data =  $this->gamesModel->where($mycond)->limit(0,$number)->order('downtimes desc')->select();
		}else if($number%$limit ==  0){
			$data =  $this->gamesModel->where($mycond)->page($curpage)->order('downtimes desc')->select();
		}else{
			$nowpage = ceil($number/$limit);
			if($page == $nowpage){
				$data = $this->gamesModel->where($mycond)->limit(($page-1)*$limit,$number%$limit)->order('downtimes desc')->select();
			}else{
				 $data =  $this->gamesModel->where($mycond)->page($curpage)->order('downtimes desc')->select();
			}
		}
		$totalcount = array('totalcount'=>$this->gamesModel->where($mycond)->count());//获取总数
		$data = array('list'=>$data);
		$data = array_merge($totalcount,$data);//组成新的数据

		return $data;
	}


	/**
	 * 上月精品游戏推荐
	 * 参数：
	 * @param number int (可选)返回记录数量
	 * @param pages int 分页数，默认为1
	 * @param rowcount int (可选) 每页返回记录数
	 * @return array data
	 */
	/*
	function getMonthPopApps($page,$number,$limit,$type){
		if($limit>0 && $limit<C('MOB_REC_PER_PAGE')){
			$curpage = $page.','.$limit;
		}else{
			$limit = C('MOB_REC_PER_PAGE');
			$curpage = $page.','.C('MOB_REC_PER_PAGE');
		}
		//去上月的时间段范围
		$timefrom = date('Y-m-d H:i:s',strtotime(date('Y-m',strtotime('-1 month'))));
		$timeto = date('Y-m-d H:i:s',strtotime(date('Y-m')));

		$cond = array();
		$cond['opt.operation'] = 0;
		$cond['app.status'] = 1;
		$cond['app.apptype'] = 1;
		$cond['opt.creatime'] = array('between',array($timefrom,$timeto));
		$cond['_string'] = 'app.id = opt.objid';

		if($number<$limit){
			$data =  $this->gamesModel->table('__OPERATION__ opt,__APP__ app')->field('app.*,count(opt.objid) as num')->where($cond)->group('opt.objid')->order('num desc')->limit(0,$number)->select();
		}else if($number%$limit == 0){
			$data =  $this->gamesModel->table('__OPERATION__ opt,__APP__ app')->field('app.*,count(opt.objid) as num')->where($cond)->group('opt.objid')->order('num desc')->page($curpage)->select();
		}else{
			$nowpage = ceil($number/$limit);
			if($page == $nowpage){
				$data =  $this->gamesModel->table('__OPERATION__ opt,__APP__ app')->field('app.*,count(opt.objid) as num')->where($cond)->group('opt.objid')->order('num desc')->limit(($page-1)*$limit,$number%$limit)->select();
			}else{
				$data =  $this->gamesModel->table('__OPERATION__ opt,__APP__ app')->field('app.*,count(opt.objid) as num')->where($cond)->group('opt.objid')->order('num desc')->page($curpage)->select();

			}
		}
		//获取实际数据总数
		$num =  $this->Model->query("select count(*) totalcount from __PREFIX__app where id in (SELECT app.id  FROM __PREFIX__operation opt,__PREFIX__app app WHERE opt.operation = 0 AND app.status = 1 AND app.apptype = 1  AND  app.id = opt.objid AND opt.creatime BETWEEN '$timefrom' AND '$timeto' GROUP BY opt.objid ORDER BY opt.objid desc )");
		$totalcount = array('totalcount'=>$num[0]['totalcount']);
		$data = array('list'=>$data);
		$data = array_merge($totalcount,$data);//组成新的数据（包含总数）
		return $data;

	}
*/

	/**
	 * 游戏APP榜单
	 * 参数：
	 * @param number int (可选)返回记录数量
	 * @param pages int 分页数，默认为1
	 * @param rowcount int (可选) 每页返回记录数
	 * @param statistictype int 类型： 1.月榜（默认） 2.周榜
	 * @param type int 1.游戏(默认) 2.应用  ** 应用类型 **
	 * @return array data
	 */
	function downloadStatistic($page,$number,$limit,$type,$statistictype)
	{
		if ($limit > 0 && $limit < C('MOB_REC_PER_PAGE')) {
			$curpage = $page . ',' . $limit;
		} else {
			$limit = C('MOB_REC_PER_PAGE');
			$curpage = $page . ',' . C('MOB_REC_PER_PAGE');
		}

		$cond = array();
		$cond['opt.operation'] = 2;
		$cond['app.status'] = 1;
		$cond['app.apptype'] = $type;

		switch ($statistictype) {
			case 1: {//月榜
				//取上月的时间段范围
				$timefrom = date('Y-m-d H:i:s', strtotime(date('Y-m', strtotime('-1 month'))));
				$timeto = date('Y-m-d H:i:s', strtotime(date('Y-m')));
				break;
			}
			case 2: {//周榜
				//去上周的时间段范围
				$now = getdate();
				if ($now['wday'] == 0) {
					$timefrom = date('Y-m-d',strtotime('-13 day'));//上星期一
					$timeto =  date('Y-m-d',strtotime('-7 day'));//上星期天
				}else{
					$timefrom =  date('Y-m-d',strtotime('-'.(6+$now['wday']).' day'));//上星期一
					$timeto =  date('Y-m-d',strtotime('-'.$now['wday'].' day'));//上星期天
				}
				break;
			}
		}

		$cond['opt.creatime'] = array('between', array($timefrom, $timeto));
		$cond['_string'] = 'app.isdel is null and app.id = opt.objid';

		if ($number < $limit) {
			$data = $this->gamesModel->table('__BOPERATION__ opt,__APP__ app')->field('app.*,count(opt.objid) as num')->where($cond)->group('opt.objid')->order('num desc')->limit(0, $number)->select();
		} else if ($number % $limit == 0) {
			$data = $this->gamesModel->table('__BOPERATION__ opt,__APP__ app')->field('app.*,count(opt.objid) as num')->where($cond)->group('opt.objid')->order('num desc')->page($curpage)->select();
		} else {
			$nowpage = ceil($number / $limit);
			if ($page == $nowpage) {
				$data = $this->gamesModel->table('__BOPERATION__ opt,__APP__ app')->field('app.*,count(opt.objid) as num')->where($cond)->group('opt.objid')->order('num desc')->limit(($page - 1) * $limit, $number % $limit)->select();
			} else {
				$data = $this->gamesModel->table('__BOPERATION__ opt,__APP__ app')->field('app.*,count(opt.objid) as num')->where($cond)->group('opt.objid')->order('num desc')->page($curpage)->select();

			}
		}
		//获取实际数据总数
		$num = $this->Model->query("select count(*) totalcount from __PREFIX__app where id in (SELECT app.id  FROM __PREFIX__boperation opt,__PREFIX__app app WHERE opt.operation = 2 AND app.status = 1 AND app.isdel is null AND app.apptype = $type  AND  app.id = opt.objid AND opt.creatime BETWEEN '$timefrom' AND '$timeto' GROUP BY opt.objid ORDER BY opt.objid desc )");
		$totalcount = array('totalcount' => $num[0]['totalcount']);
		$data = array('list' => $data);
		$data = array_merge($totalcount, $data);//组成新的数据（包含总数）
		return $data;
	}

	/**
	 * 游戏评论保存
	 * @param params array
	 * @return int result
	 */
	public function saveAppComment($params){
		$result = $this->Comment->data($params)->add();
		if($result){
			$average = $this->Comment->where('objid='.$params['objid'].' and type=2 and audit=1')->avg('score');
			$this->gamesModel->where(array('id'=>$params['objid']))->save(array('average'=>$average));
		}
		return $result;
	}

	/**
	 * 游戏评论获取
	 * @param objid : int
	 * @param pages :int 当前分页
	 * @param rowcount int 每页返回记录数 默认为系统设置
	 * @return data : array
	 */
	public function getAppComments($objid,$pages,$rowcount){
		if($rowcount>0 && $rowcount<C('MOB_REC_PER_PAGE')){
			$curpage = $pages.','.$rowcount;
		}else{
			$curpage = $pages.','.C('MOB_REC_PER_PAGE');
		}

		$params['c.objid'] = $objid;
		$params['c.type'] = 2;
		$params['c.audit'] = '1';
		$params['_string'] = 'c.userid=u.uid';
		$data = $this->Model->table('__USER__ u,__COMMENT__ c')->field('c.objid,c.userid,c.score,c.content comment,u.nickname name,UNIX_TIMESTAMP(c.creatime) time')
				->where($params)->order('c.creatime desc')->page($curpage)->select();
		foreach($data as $key=>$value){
			$data[$key]['comment'] =  preg_replace_callback('/@E(.{6}==)/', function($r) {return base64_decode($r[1]);}, $value['comment']);
		}
		$totalcount = $this->Comment->where('objid='.$objid.' and type=2 and audit=1')->count();
		$average = $this->Comment->where('objid='.$objid.' and type=2 and audit=1')->avg('score');
		return array_merge(array('totalcount'=>$totalcount),array('average'=>$average),array('list'=>$data));
	}

	/**
	 * APP相关推荐/随机应用
	 * @param $params
	 * @return array data
	 */
	/*
	public function relateRecommend($params){
		$apptype = $params['apptype'];
		$tagsid = $params['tagsid'];
		if($tagsid){
			$data = $this->Model->query("select * from __PREFIX__app where apptype=$apptype and tagsid='$tagsid' and status=1 order by rand() limit 0,".C('RELATED_RECOMMEND'));
		}else{
			$data = $this->Model->query("select * from __PREFIX__app where apptype=$apptype and status=1 order by rand() limit 0,".C('RELATED_RECOMMEND'));
		}
		return $data;
	}*/
	public function relateRecommend($id){
		$resource = $this->gamesModel->where('id='.$id.' and isdel is null and status=1')->field('id,apptype,tagsid')->find();
		if($resource){
			$apptype = $resource['apptype'];
			$tagsid = $resource['tagsid'];
			$data = $this->Model->query("select * from __PREFIX__app where apptype='$apptype' and tagsid='$tagsid' and isdel is null and status=1 order by rand() limit 0,".C('RELATED_RECOMMEND'));
			foreach($data as $key=>$value){
				if($resource['id'] == $value['id']){
					unset($data[$key]);
				}
			}
			if(count($data) == C('RELATED_RECOMMEND')){
				unset($data[count($data)-1]);
			}
			$data1 = array();
			foreach($data as $key=>$value){
				$data1[] = $value;
			}
			return $data1;
		}else{
			return array('rst'=>'-1','msg'=>'不存在相关资源');
		}

	}

	//随机应用
	public function randomApps($apptype){
		$data = $this->Model->query("select * from __PREFIX__app where apptype=$apptype and status=1 order by rand() limit 0,".((int)C('RELATED_RECOMMEND')-1));
		return $data;
	}

	function changeDownloadCount($id){
		$this->gamesModel->where(array('id' => $id))->setInc('downtimes', 1);	
		return $this->gamesModel->where(array('id' => $id))->getField('downtimes');
	}
}

