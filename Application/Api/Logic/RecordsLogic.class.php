<?php
 /*
 * created by vim.
 * User: Jason Hu
 * Date: 2016-03-07
 * Time: 18:50
 * */

namespace Api\Logic;


class recordsLogic extends \Think\Model{

	private $recordsModel;
	private $Boperation;
	private $Model;
	private $Video;
	private $App;
	private $Book;


	public function __construct(){
		$this->recordsModel = M('records');
		$this->Boperation = M('Boperation');
		$this->Model = M();
		$this->Video = M('Video');
		$this->App = M('App');
		$this->Book = M('Book');
	}

	public function addRecords($condition=array()){
		if(!empty($condition)){
			$this->recordsModel->add($condition);
		}
	}

	/**
	 * 查询用户操作
	 * 参数：
	 * $params 查询条件
	 * 用户操作类型：0.下载app 1.视频播放 2.搜索 3.阅读图书 4.打开广告 5.查询 6.打开游戏 7.打开应用 8.注册登入 9.上网 10.系统消息阅读 99.all null.不限类型
	 *//*
	public function searchOperation($params = array()){
		$cond = array();
		if(is_array($params) && count($params)>0){
			$cond = $params;
		}

		$data = null;
		if(!isset($cond['operation']) || $cond['operation'] ==  8 || $cond['operation'] == 9 || $cond['operation'] == 2 || $cond['operation'] == 5 || $cond['operation'] == 99 || $cond['operation'] == 10){
			//操作为：所有，默认，搜索，查询，注册登入，上网,阅读系统消息
			//操作表对象：operation
			$data = $this->Operation->field('uid userid,creatime,operation')->where($cond)->order('creatime desc')->select();
			return $data;
		}else{
			if(isset($cond['objid'])){
				$param['opt.objid'] = $cond['objid'];
			}
			$param['opt.uid'] = $cond['uid'];
			$param['opt.operation'] = $cond['operation'];
			$param['opt.creatime'] = array('between',array(C('DATE_FORM'),C('DATE_TO')));

			if($cond['operation'] == 0 || $cond['operation'] == 6 || $cond['operation'] == 7){
				//操作为：下载APP,打开游戏，打开应用
				$param['_string'] = 'app.id = opt.objid';
				//操作表对象：operation,app
				$data = $this->Operation->table('__APP__ app,__OPERATION__ opt')->field('opt.uid userid,opt.operation,opt.creatime,opt.public publiccolumn,app.name objname,app.filepath objurl,app.icon objqurl')
						->where($param)->order('opt.creatime desc')->select();
				return $data;
			}else if($cond['operation'] == 1){
				//操作为：视频播放
				$param['_string'] = 'video.id = opt.objid';
				//操作表对象：operation,video
				$data = $this->Operation->table('__VIDEO__ video,__OPERATION__ opt')->field('opt.uid userid,opt.operation,opt.creatime,opt.public publiccolumn,video.name objname,video.cover objmqurl,video.filepath objurl')
						->where($param)->order('opt.creatime desc')->select();
				return $data;
			}else if($cond['operation'] == 3){
				//操作为：阅读图书
				$param['_string'] = 'book.id = opt.objid';
				//操作表对象：operation,bookparam

				//$data = $this->Operation->table('__BOOKPARAM__ book,__OPERATION__ opt')->field('opt.uid userid,opt.operation,opt.creatime,opt.public publiccolumn,book.name objname,book.img objmqurl,book.chapteraddress objurl')
				//		->where($param)->order('opt.creatime desc')->select();

				$data = $this->Operation->table('__BOOK__ book,__OPERATION__ opt')->field('opt.uid userid,opt.operation,opt.creatime,opt.public publiccolumn,book.name objname,book.img objmqurl')
						->where($param)->order('opt.creatime desc')->select();
				return $data;
			}else if($cond['operation'] == 4){
				//操作为：打开广告
				$param['_string'] = 'banner.id = opt.objid';
				//操作表对象：operation,banner
				$data = $this->Operation->table('__BANNER__ banner,__OPERATION__ opt')->field('opt.uid userid,opt.operation,opt.creatime,opt.public publiccolumn,banner.name as objname,banner.img as objmqurl,banner.url as objurl')
						->where($param)->order('opt.creatime desc')->select();
				return $data;
			}
		}
	}*/

	/**
	 * 查询用户操作
	 * 参数：
	 * $params 查询条件
	 * 用户操作类型：1.播放 2.下载 3.阅读 41.视频搜索 42.APP搜索
	 */
	public function searchOperation($params,$pages,$rowcount){
		if($rowcount>0 && $rowcount<C('MOB_REC_PER_PAGE')){
			$page = $pages.','.$rowcount;
		}else{
			$page = $pages.','.C('MOB_REC_PER_PAGE');
			$rowcount = C('MOB_REC_PER_PAGE');
		}
		$totalcount = $this->Model->table('__BOPERATION__ b')->where($params)->count();
		if($params['b.operation'] == 2){//下载记录
			$params['_string'] = 'b.objid = a.id';
			$data = $this->Model->table('__APP__ a,__BOPERATION__ b')->field('b.id,b.userid,b.operation,UNIX_TIMESTAMP(b.creatime) creatime,a.id appid,a.name,a.icon objimgurl')
					->where($params)->order('b.creatime desc')->page($page)->select();
		}else if($params['b.operation'] ==1){//播放记录
			/*
			$params['_string'] = 'b.objid = v.id';
			unset($params['b.isdel']);
			$data = $this->Model->table('__VIDEO__ v,__BOPERATION__ b')->field('b.id,b.userid,b.operation,UNIX_TIMESTAMP(max(b.creatime)) creatime,b.objid videoid,b.subobjid subsetid,b.public subset,b.videonum,b.isdel,b.videolength,v.cover objimgurl,v.name')
					->where($params)->group('b.objid')->order('b.creatime desc')->select();
			unset($params['_string']);
			$totalcount = $this->Model->table('__BOPERATION__ b')->field('b.id,b.userid,b.operation,UNIX_TIMESTAMP(max(b.creatime)) creatime,b.objid videoid,b.subobjid subsetid,b.public subset,b.videonum,b.isdel,b.videolength')
					->where($params)->group('b.objid')->count();
			foreach ($data as $key=>$value) {
				if(!empty($value['isdel'])){
					unset($data[$key]);
					$totalcount-=1;
				}
			}*/
			$userid = $params['b.userid'];
			$pages = ($pages-1)*$rowcount;
			/*
			$data = $this->Model->query("select cc.id,cc.userid,cc.operation,UNIX_TIMESTAMP(cc.creatime) creatime,cc.objid videoid,cc.subobjid subsetid,cc.public subset,cc.videonum,cc.isdel,cc.videolength,dd.cover objimgurl,dd.name from
			__VIDEO__ dd join (select * from (select * from __BOPERATION__ where userid=$userid and operation=1 order by creatime desc ) as bb group by objid order by creatime desc ) as cc on
			dd.id = cc.objid where cc.isdel is null order by creatime desc limit $pages,10");
			$totalcount = $this->Model->query("select count(*) num from
			__VIDEO__ dd join (select * from (select * from __BOPERATION__ where userid=$userid and operation=1 order by creatime desc ) as bb group by objid order by creatime desc ) as cc on
			dd.id = cc.objid where cc.isdel is null ");*/
			$data = $this->Model->query("select aa.id,aa.userid,UNIX_TIMESTAMP(aa.creatime) creatime,aa.videoid,aa.operation,aa.subsetid,aa.subset,aa.videonum,aa.isdel,aa.videolength,bb.cover objimgurl,bb.name
										from __VIDEO__ bb join
										(SELECT a.id,a.userid,a.creatime,a.objid videoid,a.operation,a.subobjid subsetid,a.public subset,a.videonum,a.isdel,a.videolength
										FROM __BOPERATION__ a
										LEFT JOIN __BOPERATION__ b ON a.objid=b.objid AND a.creatime<b.creatime
										where a.objid is not null and a.operation=1 and a.userid=$userid group by a.userid,a.creatime,a.objid
										having count(b.id)<1 ORDER BY a.creatime desc) as aa
										on bb.id=aa.videoid
										where aa.isdel is null and bb.isdel is null and bb.status=1 order by creatime desc limit $pages,10");
			$totalcount = $this->Model->query("select count(*) num
										from __VIDEO__ bb join
										(SELECT a.id,a.userid,a.creatime,a.objid videoid,a.operation,a.subobjid subsetid,a.public subset,a.videonum,a.isdel,a.videolength
										FROM __BOPERATION__ a
										LEFT JOIN __BOPERATION__ b ON a.objid=b.objid AND a.creatime<b.creatime
										where a.objid is not null and a.operation=1 and a.userid=$userid group by a.userid,a.creatime,a.objid
										having count(b.id)<1 ORDER BY a.creatime desc) as aa
										on bb.id=aa.videoid
										where aa.isdel is null and bb.isdel is null and bb.status=1 ");
			/*$totalcount = $this->Model->query("select count(*) num
											  from(SELECT a.id,a.userid,a.creatime,a.objid videoid,a.operation,a.subobjid subsetid,a.public subset,a.videonum,a.isdel,a.videolength
											  FROM __BOPERATION__ a
											  LEFT JOIN __BOPERATION__ b ON a.objid=b.objid AND a.creatime<b.creatime
											  where a.objid is not null and a.operation=1 and a.userid=$userid
											  group by a.userid,a.creatime,a.objid
											  having count(b.id)<1) as bb where bb.isdel is null");*/
			$totalcount = $totalcount[0]['num'];
		}else if($params['b.operation'] == 3){//阅读
			$params['_string'] = 'b.objid = book.id';
			$data = $this->Model->table('__BOPERATION__ b,__BOOK__ book')->field('b.id,b.userid,b.operation,UNIX_TIMESTAMP(b.creatime) creatime,b.objid bookid,b.subobjid chapterid,book.name,book.img objimgurl')
					->where($params)->order('b.creatime desc')->page($page)->select();
		}else if($params['b.operation'] == 41 || $params['b.operation'] == 42){//搜索
			$data = $this->Model->table('__BOPERATION__ b')->where($params)->field('b.id,b.userid,b.operation,UNIX_TIMESTAMP(b.creatime) creatime,b.public keywords')->order('b.creatime desc')->page($page)->select();
		}
		return array_merge(array('totalcount'=>$totalcount),array('list'=>$data));
	}

	/**
	 * 关键字查询
	 * @param params array
	 * @return data array
	 */
	public function getKeywords($params){
		if($params['operation'] == '41' || $params['operation'] == '42'){
			$data = $this->Boperation->where('operation='.$params['operation'])->field('public keyword,count(public) num')
					->group('public')->order('num desc')->limit(0,14)->select();
		}else{
			$data = $this->Boperation->where('operation=41')->field('public keyword,count(public) num')
					->group('public')->order('num desc')->limit(0,7)->select();
			$data1 = $this->Boperation->where('operation=42')->field('public keyword,count(public) num')
					->group('public')->order('num desc')->limit(0,7)->select();
			if(!empty($date) && !empty($data1)){
				$data = array_merge($data,$data1);
			}else if(!empty($data1)){
				$data = $data1;
			}

		}
		return $data;
	}

	/*
	 * 保存用户操作资源记录
	 * operation : 操作类型 1：播放 2：下载 3：阅读 41：视频搜索 42.App搜索 5.查看系统消息 6.打开广告 7.分享
	 */
	public function savebOperation($param){
		$param['creatime'] = date('Y-m-d H:i:s');
		$param['factory'] = C('CUSTOMER_UNIQUE_INDEX');
		$this->Boperation->add($param);
		if($param['operation'] == C('RES_TYPE_VIDEO')){
			$this->Video->where(array('id'=>$param['objid']))->setInc('viewtimes',1);
		}else if($param['operation'] == C('RES_TYPE_APP')){
			$this->App->where(array('id'=>$param['objid']))->setInc('downtimes',1);
		}else if($param['operation'] == C('RES_TYPE_BOOK')){
			$this->Book->where(array('id'=>$param['objid']))->setInc('viewnum',1);
		}
		return true;
	}

	/**
	 * 删除用户操作记录
	 */
	public function delOperation($arr){
		$data['isdel'] =  Date('Y-m-d H:i:s');
		foreach($arr as $key=>$value){
			$id[] = $arr[$key]->id;
		}
		$params['id'] = array('in',$id);
		$result = $this->Boperation->where($params)->save($data);
		return $result;
	}
}


