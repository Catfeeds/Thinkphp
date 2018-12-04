<?php
namespace Admin\Controller;
use Think\Controller;
header("Content-Type:text/html; charset=utf-8");
class AdsController extends Controller {
    public function __construct(){
        parent::__construct();
	    $this->Category = D('Category','Logic');
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
        $this->adstype = array(1=>'图片',2=>'代码',3=>'文字');
	    $this->resourcetype =  array(1=>'详情',2=>'列表',3=>'外链');
	    $this->character =  array(1=>'游戏',2=>'积分产品',3=>'商城产品');
	    $this->apptype =  array(C('GAMES_CATEGORY')=>'游戏',C('APP_CATEGORY')=>'应用');
	    $this->fitertype =  array(1=>'最新',2=>'最热');
	    $this->model = array('');
    }

	private $Category;
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
    private $adstype;
	private $resourcetype;
	private $model;
	private $character;
	private $apptype;
	private $fitertype;

    private function checkPriv($priv){
        $adminid = session('adminid');
        if(empty($adminid)) $this->redirect('Adminuser/login',0);
        if(!session('issuper')){
            if(!empty($priv) && !in_array($priv,session('privs'))) $this->error('您没有此权限!.');
        }
        $this->assign('adname', session('name'));
    }
    //banner
    public function bannermgr(){
        $this->checkPriv('4_1_1');
        $p = getCurPage();
        //$where['end_time']=array('gt',date('Y-m-d H:i:s'));
        $res = $this->bannerLogic->getBannerList(array(),$p);
        //$type=array('图片','Flash','代码','文字');
        foreach($res as $k=>$v){
            $res[$k]['type']=$this->adstype[$res[$k]['type']];
        }
        $this->data = $res;
        $this->total = $this->bannerLogic->getBannerTotal();
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

    public function addbanner(){
        $this->checkPriv('4_1_2');
        $this->assign('act','add');
        $this->assign('errcode','0');
        if(I('post.act')=='add'){
            $newdata = array();
            $newdata['name'] = I('post.name');
            $newdata['type'] = I('post.type');
            $newdata['content'] = I('post.content');
            $newdata['sort'] = I('post.sort');
            $newdata['start_time'] = I('post.start_time');
            $newdata['end_time'] = I('post.end_time');
	        $newdata['pid'] = I('post.position');
	        $newdata['character'] = I('post.character');
	        $newdata['platform'] = I('post.platform');

	        $newdata['resourcetype'] = I('post.resourcetype');
	        if($newdata['resourcetype'] == 1){
		        $newdata['model'] = I('post.model');
	        }else if($newdata['resourcetype'] == 2){
		        $apptype = I('post.apptype');
		        $fitertype =  I('post.fitertype');
		        $appcategory =  I('post.appcategory');
		        $newdata['param'] = 'apptype/'.$apptype.';fitertype/'.$fitertype.';appcategroy/'.$appcategory;
		        //$newdata['param'] = I('post.param');
	        }else if($newdata['resourcetype'] == 3){
		        $newdata['url'] = I('post.url');
	        }
            $newdata['status']='1';

	        /*
            if($_FILES['img']['size'] > 0){
                $upres = $this->upimgfile();
                if($upres['error'] == false){
                    $newdata['img'] = $upres['result']['img']['fullpath'];
                }
            }*/
	        $img = I('post.img');
	        if(count($img)>0){
		        $newdata['img'] = $img[0];
	        }

	        $hiddenimg = I('post.hiddencurtimg');
	        if(!empty($hiddenimg)){
		        $newdata['img'] = $hiddenimg;
	        }

           // E(I('post.'));
            $ret = $this->Banner->add($newdata);
            if($ret){
                $this->redirect('Ads/bannermgr');
            }else{
                $this->error('插入数据错误');
            }
        }else{
	        $appcategory = $this->Category->getParentCategoryList(C('GAMES_CATEGORY'));
			$this->assign('appcategory',$appcategory);
	        $this->assign('apptype',$this->apptype);
	        $this->assign('fitertype',$this->fitertype);

	        $position =  $this->Position->select();
	        $resourcetype = $this->resourcetype;
	        $this->assign('platform',array('1'=>'Android','2'=>'IOS'));
	        $this->assign('resourcetype',$resourcetype);
	        $this->assign('pos',$position);
            $this->assign('types',$this->adstype);
	        $this->assign('character',$this->character);
            $this->display("Ads/banneredit");
        }
    }

    public function editbanner(){
        $this->checkPriv('4_1_3');
        $this->assign('act','edit');
        $this->assign('errcode','0');
        if(I('post.act')=='edit'){
            $newdata = array();
            $id = I('post.id','','int');
            $newdata['name'] = I('post.name');
            $newdata['type'] = I('post.type');
            $newdata['content'] = I('post.content');
            $newdata['sort'] = I('post.sort');
            $newdata['start_time'] = I('post.start_time');
            $newdata['end_time'] = I('post.end_time');
	        $newdata['pid'] = I('post.position');
	        $newdata['resourcetype'] = I('post.resourcetype');
	        $newdata['platform'] = I('post.platform');
	        if($newdata['resourcetype'] == 1){
		        $newdata['model'] = I('post.model');
	        }else if($newdata['resourcetype'] == 2){
		        $apptype = I('post.apptype');
		        $fitertype =  I('post.fitertype');
		        $appcategory =  I('post.appcategory');
		        $newdata['param'] = 'apptype/'.$apptype.';fitertype/'.$fitertype.';appcategroy/'.$appcategory;
		        //$newdata['param'] = I('post.param');
	        }else if($newdata['resourcetype'] == 3){
		        $newdata['url'] = I('post.url');
	        }
	        $newdata['character'] = I('post.character');
          //  $newdata['status']='1';

	        /*
            if($_FILES['img']['size'] > 0) {
                $upres = $this->upimgfile();
                if ($upres['error'] == false) {
                    $newdata['img'] = $upres['result']['img']['fullpath'];
                }
            }*/
	        $img = I('post.img');
	        if(count($img)>0){
		        $newdata['img'] = $img[0];
	        }

	        $hiddenimg = I('post.hiddencurtimg');
	        if(!empty($hiddenimg)){
		        $newdata['img'] = $hiddenimg;
	        }

            $ret = $this->Banner->where('id='.$id)->save($newdata);
            $this->redirect('Ads/bannermgr');

        }else{
	        $appcategory = $this->Category->getParentCategoryList(C('GAMES_CATEGORY'));
	        $this->assign('appcategory',$appcategory);
	        $this->assign('apptype',$this->apptype);
	        $this->assign('fitertype',$this->fitertype);

            $id = I('get.id','','int');
            $data = $this->bannerLogic->getBannerById($id);
	        if($data['resourcetype'] == 2){
		        $param =  preg_split('/[;|\/]/',$data['param']);
		        $data['apptype'] = $param[1];
		        $data['fitertype'] = $param[3];
		        $data['appcategory'] = $param[5];
	        }
//            $data['html_content']=htmlspecialchars($data['content']);]
            $this->data = $data;
            $this->assign('types',$this->adstype);
	        $this->assign('character',$this->character);
         //   $this->assign('simgs',json_decode($data['imgs']));
	        $imgs[] =  $data['img'];
	        if(!empty($data['img'])){
		        $this->assign('simgs',$imgs);
	        }
            $this->assign('type',$data['type']);
	        $this->assign('platform',array('1'=>'Android','2'=>'IOS'));

	        $resourcetype = $this->resourcetype;
	        $this->assign('resourcetype',$resourcetype);

	        $position =  $this->Position->select();
	        $this->assign('pos',$position);
            $this->display("Ads/banneredit");
        }
    }

    public function delbanner(){
        $this->checkPriv('4_1_4');
        $id = I('get.id','','int');
        if($id){
            $data['isdel']= date("Y-m-d H:i:s");;
            $this->Banner->where('id='.$id)->save($data);
            $from = I('server.HTTP_REFERER');
            redirect($from);
        }else{
            $this->error('该记录不存在');
        }
    }

    public function chgbannerstatus(){
	    $this->checkPriv('4_1_5');
        $id = I('get.id','','int');
        $status = I('get.status','','int');
        if($id){
            if($status == 1){
                $this->Banner->where('id='.$id)->save(array('status'=>1));
                $from = I('server.HTTP_REFERER');
                redirect($from);
            }else if($status == 2){
                $this->Banner->where('id='.$id)->save(array('status'=>2));
                $from = I('server.HTTP_REFERER');
                redirect($from);
            }else{
                $this->error('无该状态设置');
            }
        }else{
            $this->error('该记录不存在');
        }
    }
    //position
    public function positionmgr(){
        $this->checkPriv('4_2_1');
        $p = getCurPage();
        $res = $this->positionLogic->getPositionList(array(),$p);
        $this->data = $res;
        $this->total = $this->positionLogic->getPositionTotal();
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

    public function addposition(){
        $this->checkPriv('4_2_2');
        $this->assign('act','add');
        $this->assign('errcode','0');
        if(I('post.act')=='add'){
            $newdata = array();
            $newdata['title'] = I('post.title');
            $newdata['position'] = I('post.position');
            //$newdata['height'] = I('post.height');
           // $newdata['description'] = I('post.description');
            $ret = $this->Position->add($newdata);
            if($ret){
                $this->redirect('Ads/positionmgr');
            }else{
                $this->error('插入数据错误');
            }
        }else{
            $this->display("Ads/positionedit");
        }
    }

    public function editposition(){
        $this->checkPriv('4_2_3');
        $this->assign('act','edit');
        $this->assign('errcode','0');
        if(I('post.act')=='edit'){
            $newdata = array();
            $id = I('post.id','','int');
            $newdata['title'] = I('post.title');
            $newdata['position'] = I('post.position');
           // $newdata['height'] = I('post.height');
           // $newdata['description'] = I('post.description');
            $ret = $this->Position->where('id='.$id)->save($newdata);
            //if($ret){
                $this->redirect('Ads/positionmgr');
            //}else{
            //    $this->assign('errcode','1');  // 修改失败
             //   $this->error('编辑数据失败');
            //}
        }else{
            $id = I('get.id','','int');
            $data = $this->positionLogic->getPositionById($id);
            $this->data = $data;
            $this->display("Ads/positionedit");
        }
    }

    public function delposition(){
//        $this->checkPriv('1_3_4');
//        $id = I('get.id','','int');
//        if($id){
//            $data['isdel']= date("Y-m-d H:i:s");;
//            $this->Position->where('id='.$id)->save($data);
//            $from = I('server.HTTP_REFERER');
//            redirect($from);
//        }else{
//            $this->error('该记录不存在');
//        }
        $this->checkPriv('4_2_4');
        $id=I('get.id','','int');
        if($id){
            $this->Position->where('id='.$id)->delete();
            $this->redirect('Ads/positionmgr');
        }else{
            $this->error('没有该记录');
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
            $ret['error'] = true;
            $ret['result'] = $upload->getError();
            //$this->error($upload->getError());
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
        $upload->exts          = array('zip','jar','apk','doc','xls');
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

    //ecommerce
    public function ecommercemgr(){
//        $this->checkPriv('4_3_1');
//        $p = getCurPage();
//        //$where['end_time']=array('gt',date('Y-m-d H:i:s'));
//        $res = $this->bannerLogic->getBannerList(array(),$p);
//        //$type=array('图片','Flash','代码','文字');
//        foreach($res as $k=>$v){
//            $res[$k]['type']=$this->adstype[$res[$k]['type']];
//        }
//        $this->data = $res;
//        $this->total = $this->bannerLogic->getBannerTotal();
//        $show = constructAdminPage($this->total);
//        $this->assign('page',$show);
//        $this->display();

        $this->checkPriv('4_3_1');
        $p = getCurPage();
        $res = $this->bannerLogic->getBannerPid(array('position'=>'ECOMMERCE_BANNER'),$p);
        $res = $this->bannerLogic->getBannerList(array('pid'=>$res[0]['id']),$p);

        foreach($res as $k=>$v){
            $res[$k]['type']=$this->adstype[$res[$k]['type']];
        }
        $this->data = $res;
        $this->total = $this->bannerLogic->getBannerTotal();
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }


    public function addecommerce(){
        $this->checkPriv('4_3_2');
        $this->assign('act','add');
        $this->assign('errcode','0');
        if(I('post.act')=='add'){
            $newdata = array();
            $newdata['name'] = I('post.name');
            $newdata['content'] = I('post.content');
            $newdata['pid'] = I('post.position');
            if($_FILES['img']['size'] > 0){
                $upres = $this->upimgfile();
                if($upres['error'] == false){
                    $newdata['img'] = $upres['result']['img']['fullpath'];
                }
            }
            // E(I('post.'));
            $ret = $this->Banner->add($newdata);
            if($ret){
                $this->redirect('Ads/ecommercemgr');
            }else{
                $this->error('插入数据错误');
            }
        }else{
            $appcategory = $this->Category->getParentCategoryList(C('GAMES_CATEGORY'));
            $position =  $this->Position->select();
            $this->assign('pos',$position);
            $this->display("Ads/ecommerceedit");
        }
    }

    public function editecommerce(){
        $this->checkPriv('4_3_3');
        $this->assign('act','edit');
        $this->assign('errcode','0');
        if(I('post.act')=='edit'){
            $newdata = array();
            $id = I('post.id','','int');
            $newdata['name'] = I('post.name');

            $newdata['content'] = I('post.content');

            $newdata['pid'] = I('post.position');

            if($_FILES['img']['size'] > 0) {
                $upres = $this->upimgfile();
                if ($upres['error'] == false) {
                    $newdata['img'] = $upres['result']['img']['fullpath'];
                }
            }
            $ret = $this->Banner->where('id='.$id)->save($newdata);
            $this->redirect('Ads/ecommercemgr');

        }else{
            $id = I('get.id','','int');
            $data = $this->bannerLogic->getBannerById($id);
            if($data['resourcetype'] == 2){
                $param =  preg_split('/[;|\/]/',$data['param']);
                $data['apptype'] = $param[1];
                $data['fitertype'] = $param[3];
                $data['appcategory'] = $param[5];
            }
            $this->data = $data;
            $resourcetype = $this->resourcetype;

            $position =  $this->Position->select();
            $this->assign('pos',$position);
            $this->display("Ads/ecommerceedit");
        }
    }

    public function delecommerce(){
        $this->checkPriv('4_3_4');
        $id = I('get.id','','int');
        if($id){
            $data['isdel']= date("Y-m-d H:i:s");;
            $this->Banner->where('id='.$id)->save($data);
            $from = I('server.HTTP_REFERER');
            redirect($from);
        }else{
            $this->error('该记录不存在');
        }
    }

    public function chgecommercestatus(){
        $this->checkPriv('4_3_5');
        $id = I('get.id','','int');
        $status = I('get.status','','int');
        if($id){
            if($status == 1){
                $this->Banner->where('id='.$id)->save(array('status'=>1));
                $from = I('server.HTTP_REFERER');
                redirect($from);
            }else if($status == 2){
                $this->Banner->where('id='.$id)->save(array('status'=>2));
                $from = I('server.HTTP_REFERER');
                redirect($from);
            }else{
                $this->error('无该状态设置');
            }
        }else{
            $this->error('该记录不存在');
        }
    }

	public function imgJcrop(){
		$imgurl = strchr(I('post.url'),'Upload');
		$urlpos = strripos($imgurl,'?');
		if($urlpos){
			$imgurl = substr($imgurl,0,$urlpos);
		}
		$imgfileurl = $imgurl;//获取到相对路径

		$dst_h = 222;
		$dst_w = 1242;

		$postfix = strchr($imgurl,'.');//获取后缀

		//组成剪切图保存路径
		$newurlpos = strripos($imgurl,'.');
		$newimgurl = substr($imgurl,0,$newurlpos).'_cut'.$postfix;

		$dst_r = ImageCreateTrueColor( $dst_w, $dst_h );//创建背景图
		$jpeg_quality = 90;

		if($postfix == '.png'){
			$img_r = imagecreatefrompng($imgfileurl);
			imagecopyresampled($dst_r,$img_r,0,0,I('post.x'),I('post.y'),
					$dst_w,$dst_h,I('post.w'),I('post.h'));//将一幅图片复制到另一个图上之上
			imagepng($dst_r,$newimgurl);
		}elseif($postfix == '.jpeg'){
			$img_r = imagecreatefromjpeg($imgfileurl);
			imagecopyresampled($dst_r,$img_r,0,0,I('post.x'),I('post.y'),
					$dst_w,$dst_h,I('post.w'),I('post.h'));//将一幅图片复制到另一个图上之上
			imagejpeg($dst_r,$newimgurl,$jpeg_quality);
		}

		$this->ajaxReturn($newimgurl);


	}

}