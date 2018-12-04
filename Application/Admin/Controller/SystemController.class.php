<?php
namespace Admin\Controller;
use Think\Controller;
header("Content-Type:text/html; charset=utf-8");
class SystemController extends Controller
{
    public function __construct(){
        parent::__construct();
        $this->AdminuserLogic =  D('Adminuser','Logic');
        $this->Admin =  M('Admin');
        $this->Admingroup =  M('Admingroup');
	    $this->Plifeapp = M('Plifeapp');
	    $this->appObj = new \Vendor\apkparser\ApkParser();
	    $this->Corner = D('Corner','Logic');
	    $this->cornerlist = array(array('id'=>1,'name'=>'视频'),array('id'=>2,'name'=>'应用'),array('id'=>3,'name'=>'购物商城'),array('id'=>4,'name'=>'积分商城'));
     }

	private $AdminuserLogic ;
	private $Admin ;
	private $Admingroup ;
	private $Plifeapp;
	private $appObj;
	private $cornerlist;
	private $Corner;

	private function checkPriv($priv){
		$adminid = session('adminid');
		if(empty($adminid)) $this->redirect('Adminuser/login',0);
		if(!session('issuper')){
			if(!empty($priv) && !in_array($priv,session('privs'))) $this->error('您没有此权限!.');
		}
		$this->assign('adname', session('name'));
	}

	/**
	 * APP历史版本
	 */
	public function appmgr(){
		$this->checkPriv('9_3_1');
		$p = getCurPage();
		$pages = $p.','.C('ADMIN_REC_PER_PAGE');
		$res = $this->Plifeapp->where(array('name'=>'派生活'))->order('creatime desc')->page($pages)->select();
		$this->data = $res;
		$this->total = $this->Plifeapp->where(array('name'=>'派生活'))->count();
		$show = constructAdminPage($this->total);
		$this->assign('page',$show);
		$this->display();
	}

	/**
	 * 增加新版本
	 */
	public function appadd(){
		$this->checkPriv('9_3_2');
		$this->assign('act','add');
		$this->assign('errcode','0');
		if(I('post.act')=='add'){
			$newdata = array();
			$newdata['name'] = I('post.name');
			$newdata['slogon'] = I('post.slogon');
			$newdata['pubdate'] = I('post.pubdate');
			$newdata['size'] = I('post.size');

			$newdata['intro'] = I('post.intro');
			$newdata['recommendtxt'] = I('post.recommendtxt');
			$newdata['updatetxt'] = I('post.updatetxt');

			if($_FILES['iconimg']['size']>0 || $_FILES['bigiconimg']['size']>0) {
				$upres = $this->upimgfile();
				if ($upres['error'] == false) {
					$newdata['icon'] = $upres['result']['iconimg']['fullpath'];
					$newdata['cover'] = $upres['result']['bigiconimg']['fullpath'];
				}
			}
			$newdata['filepath'] =  I('post.filepath');

			$imgs = I('post.img');
			$newdata['imgs'] = json_encode($imgs);

			/*
			$a =  array_keys(array_map('trim',$newdata),'');
			if($a){
				$this->error('带*号必填项目不能为空');
			}*/

			$ret = $this->Plifeapp->add($newdata);
			if($ret){
				$this->redirect('System/appmgr');
			}else{
				$this->error('插入数据错误');
			}
		}else{
			$this->title = '添加新版本';
			$this->display("System/appedit");
		}
	}

	/**
	 * 修改APP版本
	 */
	public function editapp(){
			$this->checkPriv('9_3_3');
			$this->assign('act','edit');
			$this->assign('errcode','0');
			if(I('post.act')=='edit'){
				$newdata = array();
				$id = I('post.id','','int');
				$newdata['name'] = I('post.name');
				$newdata['slogon'] = I('post.slogon');
				$newdata['pubdate'] = I('post.pubdate');
				$newdata['size'] = I('post.size');
				$newdata['intro'] = I('post.intro');
				$newdata['recommendtxt'] = I('post.recommendtxt');
				$newdata['updatetxt'] = I('post.updatetxt');

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
				$imgs = I('post.img');
				$newdata['imgs'] = json_encode($imgs);

				/*
				$a =  array_keys(array_map('trim',$newdata),'');
				if($a){
					$this->error('带*号必填项目不能为空');
				}*/

				$ret = $this->Plifeapp->where(array('id'=>$id))->save($newdata);
				if($ret !== false){
					$this->redirect('System/appmgr');
				}else{
					$this->assign('errcode','1');  // 修改失败
					$this->display('System/appmgr');
				}
			}else{
				$id = I('get.id','','int');
				$data = $this->Plifeapp->getById($id);
				$this->assign('simgs',json_decode($data['imgs']));

				$this->assign('files',array($data['filepath']));
				$this->data = $data;

				$this->title = '编辑APP版本';
				$this->display("System/appedit");
			}
	}

	public function delapp(){
		$this->checkPriv('9_3_4');
		$id = I('get.id','','int');
		if($id){
			$data['isdel']= date("Y-m-d H:i:s");;
			$this->Plifeapp->where('id='.$id)->save($data);
			$from = I('server.HTTP_REFERER');
			redirect($from);
		}else{
			$this->error('该记录不存在');
		}
	}

	public function chgappstatus(){
		$this->checkPriv('9_3_5');
		$id = I('get.id','','int');
		$status = I('get.status','','int');
		if($id){
			if($status == 1){
				$this->Plifeapp->where('id='.$id)->save(array('status'=>1));
				$from = I('server.HTTP_REFERER');
				redirect($from);
			}else if($status == 2){
				$this->Plifeapp->where('id='.$id)->save(array('status'=>2));
				$from = I('server.HTTP_REFERER');
				redirect($from);
			}else{
				$this->error('无该状态设置');
			}
		}else{
			$this->error('该记录不存在');
		}
	}

	public function getAPK(){
		$this->checkPriv('9_3_6');
		$id = I('get.id','','int');
		if($id){
			$result = $this->Plifeapp->getById($id);
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

			$ret = $this->Plifeapp->where(array('id'=>$id))->save($params);

			if($ret !== false){
				$from = I('server.HTTP_REFERER');
				redirect($from);
			}else{
				$this->assign('errcode','1');  // 修改失败
				$this->display('System/appmgr');
			}
		}else{
			$this->error('资源ID不能为空');
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


    public function rolemgr()
    {
        $this->checkPriv('9_1_1');
        $p = getCurPage();
        $res = $this->AdminuserLogic->getAdminGroupList(array(),$p);
        $this->data = $res;
        $this->total = $this->AdminuserLogic->getAdminGroupTotal();
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

    public function addrole()
    {
        $this->checkPriv('9_1_2');
        $this->assign('errcode', '0');
        $this->assign('act', 'add');
        if (I('post.act') == 'add') {
            $groupname = I('post.groupname');
            $cond = array('groupname' => $groupname);
            $ginfo = $this->Admingroup->where($cond)->select();
            if ($ginfo) {
                $this->assign('errcode', '1');  // 用户角色已存在
                $this->data = I('post.');
                $this->display('System/roleedit');
            } else {
                $newdata = array();
                $newdata['groupname'] = I('post.groupname');
                $newdata['groupdesc'] = I('post.groupdesc');
                $this->Admingroup->add($newdata);
                $this->redirect('System/rolemgr');
            }
        } else {
            $this->display("System/roleedit");
        }
    }

    public function editrole()
    {
        $this->checkPriv('9_1_3');
        $this->assign('act', 'edit');
        $this->assign('errcode', '0');
        if (I('post.act') == 'edit') {
            $groupname = I('post.groupname');
            $id = I('post.id', '', 'int');
            $cond = array();
            $cond['groupname'] = $groupname;
            $cond['id'] = array('neq', $id);
            $ret = $this->Admingroup->where($cond)->find();
            if ($ret) {
                $this->assign('errcode', '1'); // 已经有同名用户角色
                $this->data = I('post.');
                $this->display("System/roleedit");
            } else {
                $newdata = array();
                $newdata['groupname'] = $groupname;
                $newdata['groupdesc'] = I('post.groupdesc');
                $this->Admingroup->where('id=' . $id)->save($newdata);
                $this->redirect('System/rolemgr');
            }
        } else {
            $id = I('get.id', '', 'int');
            if ($id) {
                $this->data = $this->Admingroup->getById($id);
                $this->display("System/roleedit");
            } else {
                $this->error('无效记录');
            }
        }
    }

    public function delrole()
    {
        $this->checkPriv('9_1_4');
        $id=I('get.id','','int');
        if($id){
            $this->AdminuserLogic->delAdminGroup($id);
            $from = I('server.HTTP_REFERER');
            redirect($from);
        }else{
            $this->error('没有该记录');
        }
    }

    public function rolepriv()
    {
        $this->checkPriv('9_1_5');
        $this->assign('act','priv');
        $id = I('get.id','','int');
        if(I('post.act')=='priv'){
            $id = I('post.id','','int');
            $privs = I('post.priv');
            if($privs){
                $privstr = implode(',',$privs);
                $save = array('priv'=>$privstr);
                $this->Admingroup->where('id='.$id)->save($save);
                $this->redirect('System/rolemgr');
            }else{
                $this->assign('errcode','1');
            }
        }else{
            $groupinfo = $this->Admingroup->getById($id);
            $privs = explode(',',$groupinfo['priv']);
            unset($groupinfo['priv']);
            foreach($privs as $v){
                $groupinfo['priv'][$v] = 'checked';
            }
            $this->assign('groupinfo',$groupinfo);
        }
        $this->assign('id',$id);
        $this->display();
    }

    public function adminusermgr(){
	    $this->checkPriv('9_2_1');
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $data = pathinfo($url);
        $backurl = $data['dirname'].'/'.'usersmgr';
        $this->checkPriv('9_2_1');
        $cond = array('Admin.issuper' => 0);
        $Admin = D('AdminuserView');
        $p = getCurPage();
        $this->total = (int)$Admin->where($cond)->count();
        $show = constructAdminPage($this->total);
        $this->assign('page', $show);
        $pstr = $p . ',' . C('ADMIN_REC_PER_PAGE');
        $data = $Admin->page($pstr)->where($cond)->order('id desc')->select();
        foreach ($data as &$d) {
            if (!$d['groupid'] || empty($d['groupname'])) {
                $d['groupname'] = '暂未分配';
            }
        }
        $this->data = $data;
        $agl = D('Adminuser','Logic');
        $this->assign('backurl',$backurl);
        $this->assign('admgrp',$agl->getAllAdminGroup());
        $this->display();
    }

    public function addadminuser()
    {
        $this->checkPriv('9_2_2');
        $this->assign('errcode', '0');
        $this->assign('act', 'add');
        if (I('post.act') == 'add') {
            $username = I('post.username');
            $cond = array('username' => $username);
            $uinfo = $this->Admin->where($cond)->select();
            if ($uinfo) {
                $this->assign('errcode', '1');  // 用户已存在
                $this->data = I('post.');
                $this->display('System/adminuseredit');
            } else {
                $newdata = array();
                $newdata['username'] = I('post.username');
                $newdata['nickname'] = I('post.nickname');
                $newdata['salt'] = getsalt();
                $newdata['password'] = TransPassUseSalt(I('post.password'), $newdata['salt']);
                $this->Admin->add($newdata);
                $this->redirect('System/adminusermgr');
            }
        } else {
            $this->display("System/adminuseredit");
        }
    }

    public function editadminuser()
    {
        $this->checkPriv('9_2_3');
        $this->assign('act', 'edit');
        $this->assign('errcode', '0');
        if (I('post.act') == 'edit') {
            $username = I('post.username');
            $id = I('post.id', '', 'int');
            $cond = array();
            $cond['username'] = $username;
            $cond['uid'] = array('neq', $id);
            $ret = $this->Admin->where($cond)->find();
            if ($ret) {
                $this->assign('errcode', '1'); // 已经有同名用户
                $this->data = I('post.');
                $this->display("System/adminuseredit");
            } else {
                $newdata = array();
                $newdata['username'] = $username;
                $newdata['nickname'] = I('post.nickname');
                $npw = trim(I('post.password'));
                if ($npw) {
                    $newdata['salt'] = getsalt();
                    $newdata['password'] = TransPassUseSalt($npw, $newdata['salt']);
                }
                $this->Admin->where('uid=' . $id)->save($newdata);
                $this->redirect('System/adminusermgr');
            }
        } else {
            $id = I('get.id', '', 'int');
            if ($id) {
                $this->data = $this->Admin->getByUid($id);
                $this->display("System/adminuseredit");
            } else {
                $this->error('无效记录');
            }
        }
    }

    public function chgadminuserstatus()
    {
        $this->checkPriv('9_2_4');
        $id = I('get.id','','int');
        if ($id && I('get.status') != '') {
            $newdata['status'] = I('get.status','','int');
            $this->Admin->where('uid=' . $id)->save($newdata);
        }
        $from = I('server.HTTP_REFERER');
        redirect($from);
    }

    public function chgadminusergrp(){
        $this->checkPriv('9_2_5');
        $selgrpid = I('post.selgroup','','int');
        $adminid = I('post.chgadmid','','int');
        if($selgrpid && $adminid){
            $newdata['privgid'] = $selgrpid;
            $this->Admin->where('uid='.$adminid)->save($newdata);
            $from = I('server.HTTP_REFERER');
            redirect($from);
        }else{
            $this->error('异常错误');
        }
    }

    //morganzhao
    public function usersmgr(){
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $token = $_GET['token'];
        $data = $this->publicDecrypt($token);

        $object = new \Curl\Curl\Curl();
        $object->get("http://user.pinet.co/api/get_user_data", array('token' =>$token));
        if($object->http_status_code == 200) {
            $response = json_decode($object->response);
        }
        print_r($response);die;
        $newdata = array();
        $newdata['username'] = $response->username;
        $newdata['token'] = $token;
        $newdata['nickname'] = $response->last_name.$response->first_name;
        $newdata['uid'] = $response->uid;
        $newdata['email'] = $response->email;
        $newdata['salt'] = getsalt();
        $newdata['password'] = TransPassUseSalt($response->username, $newdata['salt']);
        $this->Admin->add($newdata);
        $this->redirect('System/adminusermgr');
        print_r($response);
    }

    public function get_default($arr, $key, $default = '') {
        if(is_object($arr))
            return isset($arr->$key)? $arr->$key: $default;
        if(is_array($arr))
            return isset($arr[$key])? $arr[$key]: $default;
        return $default;
    }
    public function publicDecrypt($message) {
        $this->keyPath = 'application/config/encryptor.key';
        $this->publicKeyPath = 'application/config/encryptor_public.key';
        $this->iv = mcrypt_create_iv(32, MCRYPT_DEV_URANDOM);

        if($this->keyPath && file_exists($this->keyPath)) {
            $this->privateKey = file_get_contents($this->keyPath);
        }
        else {
            $this->logger->debug('No private key or public set for this encryptor.');
        }

        if($this->publicKeyPath && file_exists($this->publicKeyPath)) {
            $this->publicKey = file_get_contents($this->publicKeyPath);
        }
        else {
            $this->logger->debug('No public key or public set for this encryptor.');
        }
        $key = $this->get_default($this, 'publicKey');
        if($key) {
            $ret = '';
            if(openssl_public_decrypt(base64_decode($message), $ret, $key)) {
                return $ret;
            }
        }
        return false;
    }

    public function logout(){
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $data = pathinfo($url);
        $back_url = 'http://user.pinet.co/api/logout?appid=4000&template=user&callback='.$data['dirname'].'/adminusermgr.html';
        session(null);
        header('Location: '.$back_url.'');

    }

    public function mi()
    {
        $this->checkPriv('9_3_0');
        $this->assign('errcode', '0');
        $this->assign('act', 'add');
        if (I('post.act') == 'add') {
            $user = $this->AdminuserLogic->getUserId(I('post.email'));
            if(!$user){
                $this->error('此邮箱尚未注册');
            }
            $regtime = time();
            $token = md5($user['username'].$user['salt'].$regtime);
            $token_exptime = time()+60*1;
            $newdata['token_exptime'] = $token_exptime;
            $newdata['uid'] = $user['uid'];
            $this->Admin->save($newdata);
            $url = 'http://' . $_SERVER['HTTP_HOST'] . U('System/reset',array('uid'=>$user['uid'],'email'=>'530053776@qq.com','token'=>$token));

            $content = '<span style="color:#009900;">请点击以下链接找回密码，如无反应，请将链接地址复制到浏览器中打开(下次登录前有效)</span>' . "<br/>" . $url . "<br/>" . C('WEB_SITE') . "系统自动发送--请勿直接回复<br/>" . date('Y-m-d H:i:s', TIME()) . "</p>";
            SendMail('530053776@qq.com','密码找回',$content);die;
            $this->success("邮件发送成功",U('System/adminusermgr'));
        } else {

            $this->display("System/mi");
        }
    }
    public function reset(){
        $email = I('get.email');
        $user = $this->AdminuserLogic->getUserId(I('get.email'));
        $nowtime = time();
        if($nowtime>$user['token_exptime']){ //24hour
            $this->error('您的激活有效期已过，请登录您的帐号重新发送激活邮件.',U('System/adminusermgr'));
        }
        if(IS_POST){
            $user = $this->AdminuserLogic->getUserId(I('post.email'));
            $uid = $user['uid'];
            if(!$uid){
                $this->error("用户名或邮箱错误");
            }
            $newdata['salt'] = getsalt();
            $newdata['password'] = TransPassUseSalt(I('post.password_confirm'), $newdata['salt']);
            $newdata['uid'] = $uid;
            $this->Admin->save($newdata);
            $this->success("密码修改成功",U('System/adminusermgr'));

        }else{
            $this->assign('email',$email);
            $this->display("System/resetpasswd");
        }

    }

	/**
	 * 角标列表
	 */
	public function cornermgr(){
		$this->checkPriv('9_4_1');
		$p = getCurPage();
		$params = array();

		$type = I('get.type','','int');
		if(!empty($type)){
			$params['type'] = $type;
		}

		$data =  $this->Corner->getCornerList($params,$p);
		$this->total = $this->Corner->getTotalCorner($params);

		$this->assign('data',$data);
		$show = constructAdminPage($this->total);
		$this->assign('page',$show);

		$this->display();

	}

	/**
	 * 增加角标
	 */
	public function addCorner(){
		$this->checkPriv('9_4_2');
		$this->assign('errcode', '0');
		$this->assign('act', 'add');
		if (I('post.act') == 'add') {
			$newdata = array();
			$newdata['name'] = I('post.name');
			$newdata['type'] = I('post.type');

			if($_FILES['img']['size']>0) {
				$upres = $this->upimgfile();
				if ($upres['error'] == false) {
					$newdata['img'] = $upres['result']['img']['fullpath'];

				}
			}
			/*$a =  array_keys(array_map('trim',$newdata),'');
			if($a){
				$this->error('带*号必填项目不能为空');
			}*/

			$ret = $this->Corner->add($newdata);
			if($ret){
				$this->redirect('System/cornermgr');
			}else{
				$this->error('插入数据错误');
			}
		}else{
			$this->title = '添加角标';
			$this->assign('cornerlist',$this->cornerlist);
			$this->display("System/corneredit");
		}
	}

	/**
	 * 修改角标
	 */
	public function editCorner(){
		$this->checkPriv('9_4_3');
		$this->assign('errcode', '0');
		$this->assign('act', 'edit');

		if (I('post.act') == 'edit') {
			$newdata = array();
			$newdata['name'] = I('post.name');
			$newdata['type'] = I('post.type');
			$id = I('post.id',null,'int');

			if($_FILES['img']['size']>0) {
				$upres = $this->upimgfile();
				if ($upres['error'] == false) {
					$newdata['img'] = $upres['result']['img']['fullpath'];

				}
			}
			/*$a =  array_keys(array_map('trim',$newdata),'');
			if($a){
				$this->error('带*号必填项目不能为空');
			}*/

			$ret = $this->Corner->where(array('id'=>$id))->save($newdata);
			if($ret !== false){
				$this->redirect('System/cornermgr');
			}else{
				$this->error('修改数据错误');
			}
		}else{
			$id = I('get.id',null,'int');
			$data =  $this->Corner->field(true)->where(array('id'=>$id))->find();
			$this->title = '修改角标';
			$this->assign('cornerlist',$this->cornerlist);
			$this->assign('data',$data);
			$this->display("System/corneredit");
		}

	}

	/**
	 * 删除角标
	 */
	public function delcorner(){
		$this->checkPriv('9_4_4');
		$id = I('get.id','','int');
		if($id){
			$data['isdel']= date("Y-m-d H:i:s");;
			$this->Corner->where('id='.$id)->save($data);
			$from = I('server.HTTP_REFERER');
			redirect($from);
		}else{
			$this->error('该记录不存在');
		}
	}

	public function chgcornerstatus(){
		$this->checkPriv('9_4_5');
		$id = I('get.id','','int');
		$status = I('get.status','','int');
		if($id){
			if($status == 1){
				$this->Corner->where('id='.$id)->save(array('status'=>1));
				$from = I('server.HTTP_REFERER');
				redirect($from);
			}else if($status == 2){
				$this->Corner->where('id='.$id)->save(array('status'=>2));
				$from = I('server.HTTP_REFERER');
				redirect($from);
			}else{
				$this->error('无该状态设置');
			}
		}else{
			$this->error('该记录不存在');
		}
	}

}