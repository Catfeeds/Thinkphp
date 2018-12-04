<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Page;
header("Content-Type:text/html; charset=utf-8");
class ResourceController extends Controller {
    public function __construct(){
        parent::__construct();
        $this->categoryLogic=  D('Category','Logic');
        $this->commentLogic=  D('Comment','Logic');
        $this->dnsLogic=  D('Dns','Logic');
        $this->positionLogic =  D('Position','Logic');
        $this->bannerLogic =  D('Banner','Logic');
        $this->videoLogic =  D('Video','Logic');
        $this->appsLogic =  D('Apps','Logic');
        $this->Video=  M('Video');
        $this->Apps=  M('App');
        $this->Banner=  M('Banner');
        $this->Position=  M('Position');
        $this->Dns=  M('Dns');
        $this->Comment=  M('Comment');
        $this->Category=  M('Category');
	    $this->VideoTV= M('VideoTv');
	    $this->Corner = D('Corner','Logic');
	    $this->appObj = new \Vendor\apkparser\ApkParser();
    }

	private $Corner;
    private $categoryLogic;
    private $commentLogic;
    private $dnsLogic;
    private $positionLogic ;
    private $bannerLogic ;
    private $Position ;
    private $Banner ;
    private $videoLogic ;
    private $Video ;
    private $appsLogic ;
    private $Apps ;
    private $Dns;
    private $Comment;
    private $Category;
	private $appObj;
	private $VideoTV;

    private function checkPriv($priv){
        $adminid = session('adminid');
        if(empty($adminid)) $this->redirect('Adminuser/login',0);
        if(!session('issuper')){
            if(!empty($priv) && !in_array($priv,session('privs'))) $this->error('您没有此权限!.');
        }
        $this->assign('adname', session('name'));
    }

	public function getAPK(){
		$this->checkPriv('1_2_8');
		$id = I('get.id','','int');
		if($id){
			$result = $this->appsLogic->getAppsById($id);
			$targetFile = $result['filepath'];
			$filepath = __ROOT__.'/'.$targetFile;
			$res   = $this->appObj->open($targetFile);

			$params = Array();
			$pkg = $this->appObj->getPackage();
			if(!empty($pkg)){
				$params['pkg'] = $pkg;
			}
			$version = $this->appObj->getVersionName();
			if(!empty($version)){
				$params['version'] = $version;
			}
			$versioncode = $this->appObj->getVersionCode();
			if(!empty($versioncode)){
				$params['versioncode'] = $versioncode;
			}

			$ret = $this->Apps->where(array('id'=>$id))->save($params);

			if($ret !== false){
				$from = I('server.HTTP_REFERER');
				redirect($from);
			}else{
				$this->assign('errcode','1');  // 修改失败
				$this->display('Resource/appmgr');
			}


		}else{
			$this->error('资源ID不能为空');
		}
	}
    // 视频
    public function videomgr(){
        $this->checkPriv('1_1_1');
        $p = getCurPage();
        $res = $this->videoLogic->getVideoList(array(),$p);
        $this->data = $res;
        $this->total = $this->videoLogic->getVideoTotal();
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

	//视频关键字筛选
	public function videomgrkeywords(){
		$this->checkPriv('1_1_1');
		$p = getCurPage();
		$keywords = trim(I('get.keywords'));
		$pos = strpos($keywords,'.html');
		$keywords = substr($keywords,0,$pos);
		$params['name'] = array('like',"%$keywords%");
		$res = $this->videoLogic->getVideoList($params,$p);
		$this->data = $res;
		$this->total = $this->videoLogic->getVideoTotal($params);
		$show = constructAdminPage($this->total);
		$this->assign('page',$show);
		$this->display('Resource/videomgr');
	}

	//游戏关键字筛选
	public function gamesmgrkeywords(){
		$this->checkPriv('1_2_1');
		$p = getCurPage();
		$keywords = trim(I('get.keywords'));
		$pos = strpos($keywords,'.html');
		$keywords = substr($keywords,0,$pos);
		$params['name'] = array('like',"%$keywords%");
		$res = $this->appsLogic->getGameList($params,$p);
		$this->data = $res;
		$this->total = $this->appsLogic->getGameTotal($params);
		$show = constructAdminPage($this->total);
		$this->assign('page',$show);
		$this->display('Resource/gamemgr');
	}

    /* vedioplay modify by morgan 2016-01-04 */
   /* public function videoplay(){
        $uuid=I('get.uuid');
        $name=I('get.name');
        $type=I('get.type','','int');
	    $filepath = I('get.filepath');
        $setnum=I('get.setnum','','int');;
        if($type==2){
            if($setnum){
                $uuid=$uuid.'/'.$setnum;
            }else{
                $uuid=$uuid.'/'.'1';
            }
        }else{
            $uuid;
        }
	    $this->assign('filepath',$filepath);
        $this->assign('setnum',$setnum);
        $this->assign('uuid',$uuid);
        $this->assign('name',$name);
        $this->display("Resource/videoplay");
    }*/

	public function videoplay(){
		$id = trim(I('get.id'));
		$ret = $this->Video->where(array('id='.$id))->select();
		if($ret){
			$this->assign('name',$ret[0]['name']);
			$this->assign('filepath',$ret[0]['filepath']);
		}else{
			$this->error('不存在该资源');
		}
		$this->display("Resource/videoplay");
	}

    //vedio_isrecommend added by morgan 2016-01-04
    public function vedio_isrecommend(){
	    $this->checkPriv('1_1_5');
        $id = I('get.id','','int');
        $isrecommend = I('get.isrecommend','','int');
        $redirect_url = I('server.HTTP_REFERER');
        if($id){
            if($isrecommend==1 || $isrecommend==0) {
                $this->Video->where('id=' . $id)->save(array('isrecommend' => $isrecommend));
                redirect($redirect_url);
            }else{
                $this->error('无法设置该推荐视频');
            }
        }else{
            $this->error('该记录不存在');
        }
    }

    public function addvideo(){
        $this->checkPriv('1_1_2');
        $this->assign('act','add');
        $this->assign('errcode','0');
        if(I('post.act')=='add'){

            $newdata = array();
            $newdata['name'] = I('post.name');
           //$newdata['type'] = 1;
            $newdata['duratime'] = I('post.duratime');
            $newdata['director'] = I('post.director');
            $newdata['actors'] = I('post.actors');
            $newdata['setnum'] = I('post.setnum','','int');
           // $newdata['country'] = I('post.country');
            //$newdata['category'] = I('post.cate');
            $newdata['years'] = I('post.years','','int');
            $newdata['intro'] = I('post.intro');
            $newdata['uuid'] = $this->videoLogic->getUuid();

	       // $newdata['provider'] = I('post.provider');

	       // $imgs = I('post.img');
	       // $newdata['imgs'] = json_encode($imgs);
	        $newdata['videotype'] = I('post.cate','','int');

	        $val = explode(',',I('post.category_two'));
	        $newdata['categoryid'] = $val[0];
	        $newdata['category'] = $val[1];

	        $val = explode(',',I('post.country'));
	        $newdata['countrycid'] = $val[0];
	        $newdata['country'] = $val[1];
	        $newdata['cornerurl'] = I('post.corner');



	        /*
	        if($_FILES['coverimg']['size']>0 ||$_FILES['bigimgs']['size']>0) {
		        $upres = $this->upimgfile();
		        if ($upres['error'] == false) {
			        if(!empty($upres['result']['coverimg']['fullpath'])){
				        $newdata['cover'] = $upres['result']['coverimg']['fullpath'];
			        }
			        if(!empty($upres['result']['bigimgs']['fullpath'])){
				        $newdata['imgs'] = $upres['result']['bigimgs']['fullpath'];
			        }
		        }
	        }*/

	        $newdata['filepath'] = I('post.filepath');

	        $newdata['cover'] = "vieos/".$newdata['name']."/".$newdata['name'].'_normal.png';
	        $newdata['imgs'] = "vieos/".$newdata['name']."/".$newdata['name'].'_Big.png';

	        /*
	        if($_FILES['filepath']['size']>0) {
		        $upfile = $this->upfile();
		        if ($upfile['error'] == false) {
			        $newdata['filepath'] = $upfile['result']['filepath']['fullpath'];
		        }
	        }*/

            /*$a =  array_keys(array_map('trim',$newdata),'');
	        if($a){
		        $this->error('带*号必填项目不能为空');
	        }*/

            $ret = $this->Video->add($newdata);
            if($ret){
                $this->redirect('Resource/videomgr');
            }else{
                $this->error('插入数据错误');
            }
        }else{
            $types = $this->categoryLogic->getParentCategoryList(C('RES_TYPE_VIDEO'));//视频分类
	        sort($types);
            $this->assign('types',$types);

	        $types2 = $this->categoryLogic->getParentCategoryList(C('MOVIE_CATEGORY'));//电影分类
	        $this->assign('types2',$types2);

	        $types3 = $this->categoryLogic->getParentCategoryList(C('AREA_CATEGORY'));//区域分类
	        $this->assign('country',$types3);

	        $types4 = $this->Corner->getTypeCorner(array('type'=>1));
	        $this->assign('corner',$types4);

            $this->title = '添加视频';
            $this->display("Resource/videoedit");
        }
    }

    public function editvideo(){
        $this->checkPriv('1_1_3');
        $this->assign('act','edit');
        $this->assign('errcode','0');
        if(I('post.act')=='edit'){
            $newdata = array();
            $id = I('post.id','','int');
            $newdata['name'] = I('post.name');
            $newdata['duratime'] = I('post.duratime');
            $newdata['director'] = I('post.director');
            $newdata['actors'] = I('post.actors');
            $newdata['setnum'] = I('post.setnum','','int');
           // $newdata['country'] = I('post.country');
           // $newdata['category'] = I('post.cate');
            $newdata['years'] = I('post.years','','int');
            $newdata['intro'] = I('post.intro');

	        //$newdata['provider'] = I('post.provider');

	       // $imgs = I('post.img');
	       // $newdata['imgs'] = json_encode($imgs);
	        $newdata['videotype'] = I('post.cate','','int');

	        $val = explode(',',I('post.category_two'));
	        $newdata['categoryid'] = $val[0];
	        $newdata['category'] = $val[1];

	        $val = explode(',',I('post.country'));
	        $newdata['countrycid'] = $val[0];
	        $newdata['country'] = $val[1];
	        $newdata['cornerurl'] = I('post.corner');

	        /*
	        if($_FILES['coverimg']['size']>0 ||$_FILES['bigimgs']['size']>0) {
		        $upres = $this->upimgfile();
		        if ($upres['error'] == false) {
			        if(!empty($upres['result']['coverimg']['fullpath'])){
				        $newdata['cover'] = $upres['result']['coverimg']['fullpath'];
			        }
			        if(!empty($upres['result']['bigimgs']['fullpath'])){
				        $newdata['imgs'] = $upres['result']['bigimgs']['fullpath'];
			        }
		        }
	        }*/

	        $newdata['filepath'] = I('post.filepath');

	        $newdata['cover'] = "vieos/".$newdata['name']."/".$newdata['name'].'_normal.png';
	        $newdata['imgs'] = "vieos/".$newdata['name']."/".$newdata['name'].'_Big.png';


	        /*
	        if($_FILES['filepath']['size']>0) {
		        $upfile = $this->upfile();
		        if ($upfile['error'] == false) {
			        $newdata['filepath'] = $upfile['result']['filepath']['fullpath'];
		        }
	        }*/

	        /*$a =  array_keys(array_map('trim',$newdata),'');
	        if($a){
		        $this->error('带*号必填项目不能为空');
	        }*/

            $ret = $this->Video->where('id='.$id)->save($newdata);
            if($ret !== false){
                $this->redirect('Resource/videomgr');
            }else{
                $this->error('编辑数据失败');
            }


        }else{
            $id = I('get.id','','int');
	        $data = $this->videoLogic->getVideoById($id);
	        $this->assign('simgs',json_decode($data['imgs']));
            $this->data = $data;

            $types = $this->categoryLogic->getParentCategoryList(C('RES_TYPE_VIDEO'));//视频分类
            sort($types);
            $this->assign('types',$types);

	        $types2 = $this->categoryLogic->getParentCategoryList($data['videotype']);
	        $this->assign('types2',$types2);


	        $types3 = $this->categoryLogic->getParentCategoryList(C('AREA_CATEGORY'));//区域分类
	        $this->assign('country',$types3);

	        $types4 = $this->Corner->getTypeCorner(array('type'=>1));
	        $this->assign('corner',$types4);


	        $this->title = '编辑视频';

            $this->display("Resource/videoedit");
        }

    }

	// 视频
	public function tvvideomgr(){
		$this->checkPriv('1_1_1');
		$p = getCurPage();
		$res = $this->videoLogic->getVideoList(array('videotype'=>C('TV_CATEGORY')),$p);
		$this->data = $res;
		$this->total = $this->videoLogic->getVideoTotal(array('videotype'=>C('TV_CATEGORY')));
		$show = constructAdminPage($this->total);
		$this->assign('page',$show);
		$this->display();
	}

	public function tvaddvideo(){
		$this->checkPriv('1_1_2');
		$this->assign('act','add');
		$this->assign('errcode','0');
		if(I('post.act')=='add'){

			$newdata = array();
			$newdata['name'] = I('post.name');
			$newdata['director'] = I('post.director');
			$newdata['actors'] = I('post.actors');
			$newdata['setnum'] = I('post.setnum','','int');
			$newdata['years'] = I('post.years','','int');
			$newdata['intro'] = I('post.intro');
			$newdata['uuid'] = $this->videoLogic->getUuid();
			$newdata['videotype'] = C('TV_CATEGORY');

			$val = explode(',',I('post.category_two'));
			$newdata['categoryid'] = $val[0];
			$newdata['category'] = $val[1];

			$val = explode(',',I('post.country'));
			$newdata['countrycid'] = $val[0];
			$newdata['country'] = $val[1];


			if($_FILES['coverimg']['size']>0 ||$_FILES['bigimgs']['size']>0) {
				$upres = $this->upimgfile();
				if ($upres['error'] == false) {
					if(!empty($upres['result']['coverimg']['fullpath'])){
						$newdata['cover'] = $upres['result']['coverimg']['fullpath'];
					}
					if(!empty($upres['result']['bigimgs']['fullpath'])){
						$newdata['imgs'] = $upres['result']['bigimgs']['fullpath'];
					}
				}
			}

			$newdata['filepath'] = I('post.filepath');

			/*
			if($_FILES['filepath']['size']>0) {
				$upfile = $this->upfile();
				if ($upfile['error'] == false) {
					$newdata['filepath'] = $upfile['result']['filepath']['fullpath'];
				}
			}*/

			/*$a =  array_keys(array_map('trim',$newdata),'');
			if($a){
				$this->error('带*号必填项目不能为空');
			}*/

			$ret = $this->Video->add($newdata);
			if($ret){
				$this->redirect('Resource/tvvideomgr');
			}else{
				$this->error('插入数据错误');
			}
		}else{
			$types2 = $this->categoryLogic->getParentCategoryList(C('TV_CATEGORY'));//电影分类
			$this->assign('types2',$types2);

			$types3 = $this->categoryLogic->getParentCategoryList(C('AREA_CATEGORY'));//区域分类
			$this->assign('country',$types3);

			$this->title = '添加视频';
			$this->display("Resource/tvvideoedit");
		}
	}

	public function tveditvideo(){
		$this->checkPriv('1_1_3');
		$this->assign('act','edit');
		$this->assign('errcode','0');
		if(I('post.act')=='edit'){
			$newdata = array();
			$id = I('post.id','','int');
			$newdata['name'] = I('post.name');
			$newdata['director'] = I('post.director');
			$newdata['actors'] = I('post.actors');
			$newdata['setnum'] = I('post.setnum','','int');
			$newdata['years'] = I('post.years','','int');
			$newdata['intro'] = I('post.intro');
			$newdata['videotype'] = C('TV_CATEGORY');
			$val = explode(',',I('post.category_two'));
			$newdata['categoryid'] = $val[0];
			$newdata['category'] = $val[1];

			$val = explode(',',I('post.country'));
			$newdata['countrycid'] = $val[0];
			$newdata['country'] = $val[1];

			if($_FILES['coverimg']['size']>0 ||$_FILES['bigimgs']['size']>0) {
				$upres = $this->upimgfile();
				if ($upres['error'] == false) {
					if(!empty($upres['result']['coverimg']['fullpath'])){
						$newdata['cover'] = $upres['result']['coverimg']['fullpath'];
					}
					if(!empty($upres['result']['bigimgs']['fullpath'])){
						$newdata['imgs'] = $upres['result']['bigimgs']['fullpath'];
					}
				}
			}

			$newdata['filepath'] = I('post.filepath');
			/*
			if($_FILES['filepath']['size']>0) {
				$upfile = $this->upfile();
				if ($upfile['error'] == false) {
					$newdata['filepath'] = $upfile['result']['filepath']['fullpath'];
				}
			}*/

			/*$a =  array_keys(array_map('trim',$newdata),'');
			if($a){
				$this->error('带*号必填项目不能为空');
			}*/

			$ret = $this->Video->where('id='.$id)->save($newdata);
			if($ret !== false){
				$this->redirect('Resource/tvvideomgr');
			}else{
				$this->error('编辑数据失败');
			}


		}else{
			$id = I('get.id','','int');
			$data = $this->videoLogic->getVideoById($id);
			$this->assign('simgs',json_decode($data['imgs']));
			$this->data = $data;

			$types2 = $this->categoryLogic->getParentCategoryList($data['videotype']);
			$this->assign('types2',$types2);

			$types3 = $this->categoryLogic->getParentCategoryList(C('AREA_CATEGORY'));//区域分类
			$this->assign('country',$types3);


			$this->title = '电视剧编辑';

			$this->display("Resource/tvvideoedit");
		}

	}

	public function tvnummgr(){
		$this->checkPriv('1_1_1');
		$p = getCurPage();
		$res = $this->videoLogic->gettvnum(I('get.id','','int'),$p);
		$this->data = $res['data'];
		$this->total = $res['totalcount'];
		$show = constructAdminPage($this->total);
		$this->assign('id',I('get.id','','int'));
		$this->assign('page',$show);
		$this->display();
	}

	public function addtvnum(){
		$this->checkPriv('1_1_2');
		$this->assign('act','add');
		$this->assign('errcode','0');
		if(I('post.act')=='add'){

			$newdata = array();
			$newdata['name'] = I('post.name');
			$newdata['intro'] = I('post.intro');
			$newdata['videoid'] = I('post.videoid');
			$newdata['tvnum'] = trim(I('post.tvnum'));
			$newdata['sort'] =  I('post.sort','','int');
			$newdata['duratime'] =  trim(I('post.duratime'));

			$newdata['filepath'] = I('post.filepath');

			/*$a =  array_keys(array_map('trim',$newdata),'');
			if($a){
				$this->error('带*号必填项目不能为空');
			}*/

			$ret = $this->VideoTV->add($newdata);
			if($ret){
				$this->redirect('Resource/tvnummgr');
			}else{
				$this->error('插入数据错误');
			}
		}else{
			$id = I('get.id','','int');
			$data = $this->videoLogic->getVideoById($id);
			$this->video = $data;

			$this->title = '添加集数';
			$this->display("Resource/tvnumedit");
		}
	}

	public function edittvnum(){
		$this->checkPriv('1_1_3');
		$this->assign('act','edit');
		$this->assign('errcode','0');
		if(I('post.act')=='edit'){
			$newdata = array();
			$id = I('post.id','','int');
			$newdata['name'] = I('post.name');
			$newdata['intro'] = I('post.intro');
			$newdata['videoid'] = I('post.videoid');
			$newdata['tvnum'] = trim(I('post.tvnum'));
			$newdata['sort'] =  I('post.sort','','int');
			$newdata['duratime'] =  trim(I('post.duratime'));

			$newdata['filepath'] = I('post.filepath');

			/*$a =  array_keys(array_map('trim',$newdata),'');
			if($a){
				$this->error('带*号必填项目不能为空');
			}*/

			$ret = $this->VideoTV->where('id='.$id)->save($newdata);
			if($ret !== false){
				$this->redirect('Resource/tvnummgr');
			}else{
				$this->error('编辑数据失败');
			}
		}else{
			$id = I('get.id','','int');
			$videoid = I('get.videoid','','int');
			$video = $this->videoLogic->getVideoById($videoid);
			$this->video = $video;

			$this->data = $this->VideoTV->getById($id);

			$this->title = '电视剧集数编辑';
			$this->display("Resource/tvnumedit");
		}

	}

	public function deltvnum(){
		$this->checkPriv('1_1_4');
		$id = I('get.id','','int');
		echo $id;
		if($id){
			$data['isdel']= date("Y-m-d H:i:s");
			$this->VideoTV->where('id='.$id)->save($data);
			$from = I('server.HTTP_REFERER');
			redirect($from);
		}else{
			$this->error('该记录不存在');
		}
	}

	public function chgtvnumstatus(){
		$id = I('get.id','','int');
		$status = I('get.status','','int');
		if($id){
			if($status == 1){
				$this->VideoTV->where('id='.$id)->save(array('status'=>1));
				$from = I('server.HTTP_REFERER');
				redirect($from);
			}else if($status == 2){
				$this->VideoTV->where('id='.$id)->save(array('status'=>2));
				$from = I('server.HTTP_REFERER');
				redirect($from);
			}else{
				$this->error('无该状态设置');
			}
		}else{
			$this->error('该记录不存在');
		}
	}

	public function delvideo(){
        $this->checkPriv('1_1_4');
        $id = I('get.id','','int');
        echo $id;
        if($id){
            $data['isdel']= date("Y-m-d H:i:s");
            $this->Video->where('id='.$id)->save($data);
            $from = I('server.HTTP_REFERER');
            redirect($from);
        }else{
            $this->error('该记录不存在');
        }
    }

    public function chgvideostatus(){
	    $this->checkPriv('1_1_6');
        $id = I('get.id','','int');
        $status = I('get.status','','int');
        if($id){
            if($status == 1){
                $this->Video->where('id='.$id)->save(array('status'=>1));
                $from = I('server.HTTP_REFERER');
                redirect($from);
            }else if($status == 2){
                $this->Video->where('id='.$id)->save(array('status'=>2));
                $from = I('server.HTTP_REFERER');
                redirect($from);
            }else{
                $this->error('无该状态设置');
            }
        }else{
            $this->error('该记录不存在');
        }
    }
    // 应用
    public function appmgr(){
        $this->checkPriv('1_3_1');
        $p = getCurPage();
        $res = $this->appsLogic->getAppList(array(),$p);
        $this->data = $res;
        $this->total = $this->appsLogic->getAppTotal();
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

    public function addapp(){
        $this->checkPriv('1_3_2');
        $this->assign('act','add');
        $this->assign('errcode','0');
        if(I('post.act')=='add'){
            $newdata = array();
            $newdata['name'] = I('post.name');
            $newdata['slogon'] = I('post.slogon');
            //$newdata['pkg'] = I('post.pkg');
            $newdata['pubdate'] = I('post.pubdate');
            //$newdata['version'] = I('post.version');
            $newdata['size'] = I('post.size');
           // $newdata['tags'] = I('post.tags');
	        $val = explode(',',I('post.category_two'));
	        $newdata['tagsid'] = $val[0];
	        $newdata['tags'] = $val[1];

            $newdata['intro'] = I('post.intro');
            //$newdata['bid'] = I('post.banner');
           // $newdata['pubuser'] = I('post.pubuser');
           // $newdata['tags'] = I('post.category');
            $newdata['recommendtxt'] = I('post.recommendtxt');
            $newdata['updatetxt'] = I('post.updatetxt');
           // $newdata['apptype'] = 2;
	       // $newdata['apptype'] = I('post.category');
	        $newdata['apptype'] = C('APP_CATEGORY');

	        $newdata['provider'] = I('post.provider');

            if($_FILES['iconimg']['size']>0 || $_FILES['bigiconimg']['size']>0) {
                $upres = $this->upimgfile();
                if ($upres['error'] == false) {
                    $newdata['icon'] = $upres['result']['iconimg']['fullpath'];
	                $newdata['cover'] = $upres['result']['bigiconimg']['fullpath'];
                }
            }
	        $newdata['filepath'] =  I('post.filepath');
	        /*
            if($_FILES['filepath']['size']>0) {
                $upfile = $this->upfile();
                if ($upfile['error'] == false) {
                    $newdata['filepath'] = $upfile['result']['filepath']['fullpath'];
                }
            }*/
            $imgs = I('post.img');
            $newdata['imgs'] = json_encode($imgs);


	        /*$a =  array_keys(array_map('trim',$newdata),'');
	        if($a){
		        $this->error('带*号必填项目不能为空');
	        }*/

            $ret = $this->Apps->add($newdata);
            if($ret){
                $this->redirect('Resource/appmgr');
            }else{
                $this->error('插入数据错误');
            }
        }else{
            $where['end_time']=array('gt',date('Y-m-d H:i:s'));
            $banners = $this->bannerLogic->getBannerList($where);
            foreach($banners as $k=>$v){
                $banner_args[$k]=array('banner'=>$v['name'],'id'=>$v['id']);
            }

           // $types = $this->categoryLogic->getParentCategoryList('2');
            //$types[] = array('id'=>'0','name'=>'=请选择=');
           // sort($types);
           // $this->assign('types',$types);

	        $types2 = $this->categoryLogic->getParentCategoryList(C('APP_CATEGORY'));//应用分类
	        $this->assign('types2',$types2);

	        $provider = $this->categoryLogic->getParentCategoryList(C('PROVIDER_CATEGORY'));//应用来源分类
	        $this->assign('providerlist',$provider);

            $this->assign('banners',$banner_args);
            $this->title = '添加应用';
            $this->display("Resource/appedit");
        }
    }

    public function editapp(){
        $this->checkPriv('1_3_3');
        $this->assign('act','edit');
        $this->assign('errcode','0');
        if(I('post.act')=='edit'){
            $newdata = array();
            $id = I('post.id','','int');
            $newdata['name'] = I('post.name');
            $newdata['slogon'] = I('post.slogon');
           // $newdata['pkg'] = I('post.pkg');
            $newdata['pubdate'] = I('post.pubdate');
           // $newdata['version'] = I('post.version');
            $newdata['size'] = I('post.size');
           // $newdata['tags'] = I('post.tags');
	        $val = explode(',',I('post.category_two'));
	        $newdata['tagsid'] = $val[0];
	        $newdata['tags'] = $val[1];
	      //  $newdata['apptype'] = I('post.category');

            $newdata['intro'] = I('post.intro');
           // $newdata['bid'] = I('post.banner');
           // $newdata['pubuser'] = I('post.pubuser');
          //  $newdata['tags'] = I('post.category');
            $newdata['recommendtxt'] = I('post.recommendtxt');
            $newdata['updatetxt'] = I('post.updatetxt');

	        $newdata['provider'] = I('post.provider');

	        if($_FILES['iconimg']['size']>0 ||$_FILES['bigiconimg']['size']>0) {
		        $upres = $this->upimgfile();
		        if ($upres['error'] == false) {
			        if(!empty($upres['result']['iconimg']['fullpath'])){
				        $newdata['icon'] = $upres['result']['iconimg']['fullpath'];
			        }
			        if(!empty($upres['result']['bigiconimg']['fullpath'])){
				        $newdata['cover'] = $upres['result']['bigiconimg']['fullpath'];
			        }
		        }
	        }
	        $newdata['filepath'] =  I('post.filepath');
	        /*
            if($_FILES['filepath']['size']>0) {
                $upfile = $this->upfile();
                if ($upfile['error'] == false) {
                    $newdata['filepath'] = $upfile['result']['filepath']['fullpath'];
                }
            }*/

            $imgs = I('post.img');
            $newdata['imgs'] = json_encode($imgs);


	        /*$a =  array_keys(array_map('trim',$newdata),'');
	        if($a){
		        $this->error('带*号必填项目不能为空');
	        }*/

            $ret = $this->Apps->where(array('id'=>$id))->save($newdata);
            if($ret !== false){
                $this->redirect('Resource/appmgr');
            }else{
                $this->assign('errcode','1');  // 修改失败
                $this->display('Resource/appmgr');
            }
        }else{

            $where['end_time']=array('gt',date('Y-m-d H:i:s'));
            $banners = $this->bannerLogic->getBannerList($where);
            foreach($banners as $k=>$v){
                $banner_args[$k]=array('banner'=>$v['name'],'id'=>$v['id']);
            }

           // $types = $this->categoryLogic->getParentCategoryList('2');
            //$types[] = array('id'=>'0','title'=>'=请选择=');
          //  sort($types);
          //  $this->assign('types',$types);
            $this->assign('banners',$banner_args);
            $id = I('get.id','','int');
            $data = $this->appsLogic->getAppsById($id);
            $this->assign('simgs',json_decode($data['imgs']));

            $this->assign('files',array($data['filepath']));
            $this->data = $data;

	       // $types2 = $this->categoryLogic->getParentCategoryList('71');
	       // $this->assign('types2',$types2);
	        $types2 = $this->categoryLogic->getParentCategoryList(C('APP_CATEGORY'));//应用分类
	        $this->assign('types2',$types2);

	        $provider = $this->categoryLogic->getParentCategoryList(C('PROVIDER_CATEGORY'));//应用来源分类
	        $this->assign('providerlist',$provider);

            $this->title = '编辑应用';
            $this->display("Resource/appedit");
        }
    }

    public function delapp(){
        $this->checkPriv('1_3_4');
        $id = I('get.id','','int');
        if($id){
            $data['isdel']= date("Y-m-d H:i:s");;
            $this->Apps->where('id='.$id)->save($data);
            $from = I('server.HTTP_REFERER');
            redirect($from);
        }else{
            $this->error('该记录不存在');
        }
    }

    //dns
    public function dnsmgr(){
        $this->checkPriv('1_3_2');
        $p = getCurPage();
        $res = $this->dnsLogic->getDnsList(array(),$p);
        $this->data = $res;
        $this->total = $this->dnsLogic->getDnsTotal();
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

    public function adddns(){
        $this->checkPriv('1_3_2');
        $this->assign('act','add');
        $this->assign('errcode','0');
        if(I('post.act')=='add'){
            $newdata = array();
            $newdata['hostname'] = I('post.hostname');
            $newdata['hostip'] = I('post.hostip');
            $newdata['dns'] = I('post.dns');
            $newdata['sort'] = I('post.sort');
            $ret = $this->Dns->add($newdata);
            if($ret){
                $this->redirect('Resource/dnsmgr');
            }else{
                $this->error('插入数据错误');
            }
        }else{
            $this->display("Resource/dnsedit");
        }
    }

    public function editdns(){
        $this->checkPriv('1_3_3');
        $this->assign('act','edit');
        $this->assign('errcode','0');
        if(I('post.act')=='edit'){
            $newdata = array();
            $id = I('post.id','','int');
            $newdata['hostname'] = I('post.hostname');
            $newdata['hostip'] = I('post.hostip');
            $newdata['dns'] = I('post.dns');
            $newdata['sort'] = I('post.sort');
            $ret = $this->Dns->where('id='.$id)->save($newdata);
            if($ret !== false){
                $this->redirect('Resource/dnsmgr');
            }else{
                $this->assign('errcode','1');  // 修改失败
                $this->error('编辑数据失败');
            }
        }else{
            $id = I('get.id','','int');
            $data = $this->dnsLogic->getDnsById($id);
            $this->data = $data;
            $this->display("Resource/dnsedit");
        }
    }

    public function deldns(){
        $this->checkPriv('1_3_4');
        $id = I('get.id','','int');
        if($id){
            $data['isdel']= date("Y-m-d H:i:s");;
            $this->Dns->where('id='.$id)->save($data);
            $from = I('server.HTTP_REFERER');
            redirect($from);
        }else{
            $this->error('该记录不存在');
        }
    }


    public function chgappstatus(){
	    $this->checkPriv('1_3_6');
        $id = I('get.id','','int');
        $status = I('get.status','','int');
        if($id){
            if($status == 1){
                $this->Apps->where('id='.$id)->save(array('status'=>1));
                $from = I('server.HTTP_REFERER');
                redirect($from);
            }else if($status == 2){
                $this->Apps->where('id='.$id)->save(array('status'=>2));
                $from = I('server.HTTP_REFERER');
                redirect($from);
            }else{
                $this->error('无该状态设置');
            }
        }else{
            $this->error('该记录不存在');
        }
    }
    // 游戏
    public function gamemgr(){
        $this->checkPriv('1_2_1');
        $p = getCurPage();
        $res = $this->appsLogic->getGameList(array(),$p);
        $this->data = $res;
        $this->total = $this->appsLogic->getGameTotal();
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

    //game_isrecommend
    public function game_isrecommend(){
	    $this->checkPriv('1_2_5');
        $id = I('get.id','','int');
        $isrecommend = I('get.isrecommend','','int');
        $redirect_url = I('server.HTTP_REFERER');
        if($id){
            if(isset($isrecommend)){
                $this->Apps->where('id='.$id)->save(array('isrecommend'=>$isrecommend));
                redirect($redirect_url);
            }else{
                $this->error('无法设置该推荐游戏');
            }
        }else{
            $this->error('该记录不存在');
        }
    }

	//APP是否作为幻灯片显示
	public function slide(){
		$this->checkPriv('1_2_7');
		$id = I('get.id','','int');
		$slide = I('get.slide','','int');
		$redirect_url = I('server.HTTP_REFERER');
		if($id){
			if(isset($slide)){
				$res = $this->Apps->getById($id);
				if($res['cover'] == null){
					$this->error('该资源没有幻灯片，不能执行该操作');
				}else{
					$this->Apps->where('id='.$id)->save(array('slide'=>$slide));
					redirect($redirect_url);
				}
			}else{
				$this->error('无法将此设置为幻灯片显示');
			}
		}else{
			$this->error('该记录不存在');
		}
	}

	//视频是否作为幻灯片显示
	public function videoslide(){
		$this->checkPriv('1_1_7');
		$id = I('get.id','','int');
		$slide = I('get.slide','','int');
		$redirect_url = I('server.HTTP_REFERER');
		if($id){
			if(isset($slide)){
				$res = $this->Video->getById($id);
				if($res['imgs'] == null){
					$this->error('该资源没有幻灯片，不能执行该操作');
				}else{
					$this->Video->where('id='.$id)->save(array('slide'=>$slide));
					redirect($redirect_url);
				}
			}else{
				$this->error('无法将此设置为幻灯片显示');
			}
		}else{
			$this->error('该记录不存在');
		}
	}

    public function addgame(){
        $this->checkPriv('1_2_2');
        $this->assign('act','add');
        $this->assign('errcode','0');
        if(I('post.act')=='add'){
            $newdata = array();
            $newdata['name'] = I('post.name');
            $newdata['slogon'] = I('post.slogon');
            $newdata['pubdate'] = I('post.pubdate');
           // $newdata['pkg'] = I('post.pkg');
            //$newdata['version'] = I('post.version');
            $newdata['size'] = I('post.size');
           // $newdata['tags'] = I('post.tags');
	        $val = explode(',',I('post.category_two'));
	        $newdata['tagsid'] = $val[0];
	        $newdata['tags'] = $val[1];
            $newdata['intro'] = I('post.intro');
           // $newdata['pubuser'] = I('post.pubuser');
            //$newdata['tags'] = I('post.category');
            $newdata['recommendtxt'] = I('post.recommendtxt');
            $newdata['updatetxt'] = I('post.updatetxt');
           // $newdata['apptype'] = 70;
	       // $newdata['apptype'] = I('post.category');
	        $newdata['apptype'] = C('GAMES_CATEGORY');

	        $newdata['provider'] = I('post.provider');
	        $newdata['cornerurl'] = I('post.corner');

            /*if($_FILES['iconimg']['size']>0 ||$_FILES['bigiconimg']['size']>0) {
                $upres = $this->upimgfile();
                if ($upres['error'] == false) {
                    $newdata['icon'] = $upres['result']['iconimg']['fullpath'];
	                $newdata['cover'] =$upres['result']['bigiconimg']['fullpath'];
                }
            }*/
	        $newdata['icon'] = "packages/".$newdata['name']."/icon.jpg";
	        $newdata['cover'] = "packages/".$newdata['name']."/cover.jpg";
			for($i=0;$i<5;$i++){
				$imgs[$i] = "packages/".$newdata['name']."/imgs$i.jpg";
			}

	        $newdata['filepath'] = I('post.filepath');
	        /*
            if($_FILES['filepath']['size']>0) {
                $upfile = $this->upfile();
                if ($upfile['error'] == false) {
                    $newdata['filepath'] = $upfile['result']['filepath']['fullpath'];
                }
            }*/
           // $imgs = I('post.img');
            $newdata['imgs'] = json_encode($imgs);


	       /* $a =  array_keys(array_map('trim',$newdata),'');
	        if($a){
		        $this->error('带*号必填项目不能为空');
	        }*/

            $ret = $this->Apps->add($newdata);
            if($ret){
                $this->redirect('Resource/gamemgr');
            }else{
                $this->error('插入数据错误');
            }
        }else{
           // $types = $this->categoryLogic->getParentCategoryList('2');
            //$types[] = array('id'=>'0','name'=>'=请选择=');
            //sort($types);
            //$this->assign('types',$types);

	        $types2 = $this->categoryLogic->getParentCategoryList(C('GAMES_CATEGORY'));//游戏分类
	        $this->assign('types2',$types2);

	        $provider = $this->categoryLogic->getParentCategoryList(C('PROVIDER_CATEGORY'));//游戏来源分类
	        $this->assign('providerlist',$provider);

	        $types4 = $this->Corner->getTypeCorner(array('type'=>1));
	        $this->assign('corner',$types4);

            $this->title = '添加游戏';
            $this->display("Resource/gameedit");
        }
    }

    public function editgame(){
        $this->checkPriv('1_2_3');
        $this->assign('act','edit');
        $this->assign('errcode','0');
        if(I('post.act')=='edit'){
            $newdata = array();
            $id = I('post.id','','int');
            $newdata['name'] = I('post.name');
            $newdata['slogon'] = I('post.slogon');
           // $newdata['pkg'] = I('post.pkg');
            $newdata['pubdate'] = I('post.pubdate');
           // $newdata['version'] = I('post.version');
            $newdata['size'] = I('post.size');

	        $val = explode(',',I('post.category_two'));
	        $newdata['tagsid'] = $val[0];
	        $newdata['tags'] = $val[1];
	        //$newdata['apptype'] = I('post.category');

            $newdata['intro'] = I('post.intro');
           // $newdata['pubuser'] = I('post.pubuser');
          //  $newdata['tags'] = I('post.category');
            $newdata['recommendtxt'] = I('post.recommendtxt');
            $newdata['updatetxt'] = I('post.updatetxt');

	        $newdata['provider'] = I('post.provider');
	        $newdata['cornerurl'] = I('post.corner');

            /*
	        if($_FILES['iconimg']['size']>0 ||$_FILES['bigiconimg']['size']>0) {
                $upres = $this->upimgfile();
                if ($upres['error'] == false) {
	                if(!empty($upres['result']['iconimg']['fullpath'])){
		                $newdata['icon'] = $upres['result']['iconimg']['fullpath'];
	                }
                    if(!empty($upres['result']['bigiconimg']['fullpath'])){
	                    $newdata['cover'] = $upres['result']['bigiconimg']['fullpath'];
                    }

                }
            }*/

	        $newdata['icon'] = "packages/".$newdata['name']."/icon.jpg";
	        $newdata['cover'] = "packages/".$newdata['name']."/cover.jpg";
	        for($i=0;$i<5;$i++){
		        $imgs[$i] = "packages/".$newdata['name']."/imgs$i.jpg";
	        }

	        $newdata['filepath'] = I('post.filepath');
	        /*
            if($_FILES['filepath']['size']>0) {
                $upfile = $this->upfile();
                if ($upfile['error'] == false) {
                    $newdata['filepath'] = $upfile['result']['filepath']['fullpath'];
                }
            }*/
            //$imgs = I('post.img');
            $newdata['imgs'] = json_encode($imgs);


	        /*$a =  array_keys(array_map('trim',$newdata),'');
	        if($a){
		        $this->error('带*号必填项目不能为空');
	        }*/

            $ret = $this->Apps->where('id='.$id)->save($newdata);
            if($ret !== false){
                $this->redirect('Resource/gamemgr');
            }else{
                $this->assign('errcode','1');  // 修改失败
                $this->display('Resource/gamemgr');
            }
        }else{
            $id = I('get.id','','int');
            $data = $this->appsLogic->getAppsById($id);
            $this->assign('simgs',json_decode($data['imgs']));
            $this->data = $data;

           // $types = $this->categoryLogic->getParentCategoryList('2');
           // $types[] = array('id'=>'0','name'=>'=请选择=');
          //  sort($types);
          //  $this->assign('types',$types);
	        $types2 = $this->categoryLogic->getParentCategoryList(C('GAMES_CATEGORY'));//游戏分类
	        $this->assign('types2',$types2);

	        $provider = $this->categoryLogic->getParentCategoryList(C('PROVIDER_CATEGORY'));//游戏来源分类
	        $this->assign('providerlist',$provider);

	        $types4 = $this->Corner->getTypeCorner(array('type'=>1));
	        $this->assign('corner',$types4);

            $this->title = '游戏编辑';
            $this->display("Resource/gameedit");
        }
    }

    public function delgame(){
        $this->checkPriv('1_2_4');
        $id = I('get.id','','int');
        if($id){
            $data['isdel']= date("Y-m-d H:i:s");;
            $this->Apps->where('id='.$id)->save($data);
            $from = I('server.HTTP_REFERER');
            redirect($from);
        }else{
            $this->error('该记录不存在');
        }
    }

    public function chggamestatus(){
	    $this->checkPriv('1_2_6');
        $id = I('get.id','','int');
        $status = I('get.status','','int');
        if($id){
            if($status == 1){
                $this->Apps->where('id='.$id)->save(array('status'=>1));
                $from = I('server.HTTP_REFERER');
                redirect($from);
            }else if($status == 2){
                $this->Apps->where('id='.$id)->save(array('status'=>2));
                $from = I('server.HTTP_REFERER');
                redirect($from);
            }else{
                $this->error('无该状态设置');
            }
        }else{
            $this->error('该记录不存在');
        }
    }
    public function tmpupimgs(){
        $bimgs = $this->upimgfile();
        if($bimgs['error'] != true){
            $ret = array();
            foreach($bimgs['result'] as $img){
                $ret[] = $img['fullpath'];
            }
            $this->ajaxReturn($ret);
        }else{
            echo 0;
        }
    }

    private function upimgfile(){
        $ret = array();
        $upload =  new \Think\Upload();
        $upload->maxSize       = C('ITEM_IMG_MAXSIZE');;
        $upload->exts          = array('jpg', 'jpeg', 'png', 'gif', 'bmp');
        $upload->rootPath      = C('ITEM_IMG_PATH');
        $upload->subName       = array('date', 'Ym');
        $upfinfo = $upload->upload();

        if(!$upfinfo) {// 上传错误提示错误信息
            print_r($upfinfo);die;
            $ret['error'] = true;
            $ret['result'] = $upload->getError();
        }else{// 上传成功
            foreach($upfinfo as $k=>&$file){
                $file['fullpath'] = $upload->rootPath.$file['savepath'].$file['savename'];
            }
            $ret['error'] = false;
            $ret['result'] = $upfinfo;
        }
        return $ret;
    }



    private function upfile(){
        $ret = array();
        $upload =  new \Think\Upload();
        $upload->maxSize       = C('ITEM_FILE_SIZE');;
        $upload->exts          = array('zip','jar','apk','doc','xls','ts');
        $upload->rootPath      = C('ITEM_FILE_PATH');
        $upload->subName       = array('date', 'Ym');
        $upfinfo = $upload->upload();
        if($upfinfo) {
            foreach($upfinfo as $k=>&$file){
                $file['fullpath'] = $upload->rootPath.$file['savepath'].$file['savename'];
            }
            $ret['error'] = false;
            $ret['result'] = $upfinfo;
        }else{
            $ret['error'] = true;
            $ret['result'] = $upload->getError();
            $this->error($upload->getError());
        }
        return $ret;
    }
}
