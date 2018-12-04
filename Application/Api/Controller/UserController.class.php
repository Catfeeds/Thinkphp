<?php
namespace Api\Controller;
use Think\Controller;

class UserController extends Controller {


  /*  public function __construct(){
        parent::__construct();
        $this->AdminuserLogic = D('User','Logic');
    }

    public function UpdateUserInfo(){
        $this->post = I('post.');
        $object = new \Curl\Curl\Curl();
        $object->get("http://user.pinet.co/api/get_user_data", array('token' =>$this->post['token']));
//        print_r($object);die;
        if($object->http_status_code == 200) {
            $response = json_decode($object->response);
        }
        $response=$this->AdminuserLogic->update_userinfo($this->post,$this->post['token']);
	    //$response=$this->AdminuserLogic->update_userinfo($this->post,$this->post['token'],$response);
        $this->ajaxReturn($response);
    }
  */

	private $User;
	private $YunPian;
	private $UserTask;

	public function __construct(){
		parent::__construct();
		$this->User =  D('User','Logic');
		$this->UserTask =  D('Task','Logic');
		$this->YunPian = new \Vendor\yunpian\YunPian();
	}

	/**
	 * 发送短信验证码
	 * @param int forlogin : 默认为0  1.直接返回验证码 0.生存注册用户验证码
	 * @param string phone
	 * @param josn data
	 */
	public function sendVerifyCode(){
		$phone =  trim(I('post.phone','',string));
		$type = I('post.forlogin',null,'int');
		$reg =  '/^((13[0-9])|(14[5,7,9])|(15[0-35-9])|(17[0,1,3,5,6,7,8])|(18[0-9]))\d{8}$/';//手机号正则
		$data = array();
		$data['rst'] = '-1';
		$data['msg'] = 'failure';
		if(preg_match($reg,$phone)){//检测手机号是否正确
			if($type == 0){
				//此为注册时才进行判断
				if($this->User->findUser($phone) != null){//检查该手机号是否已经注册
					$data['msg'] = '此号码已经注册，请直接登入使用';
					$this->ajaxReturn($data);
				}
			}
			//生成随机4位验证码
			$code = '';
			for($i=1;$i<=4;$i++){
				$code.=mt_rand(0,9);
			}
			//发送验证码
			$result = $this->YunPian->sendSMS($code,$phone);
			if($result['code'] == 0){
				$this->User->addCode($code,$phone);//保存验证码
				$data['rst'] = '0';
				$data['msg'] = '验证发送成功，请注意查看';
			}else{
				//发送短信失败，生成错误日志
				$errorstr_com = '['.date('Y-m-d H:m:i',time()).']注册新用户,发送短信失败.';
				$errorstr = $errorstr_com.'Error code is:'.$result[code].',Error message is:'.$result['msg'].',Error detail is:'.$result['detail'].".\r\n";

//				//获取当前厂区信息
//				$customerid = 3;
//				$customer = $this->User->getCustomer($customerid);

				$customer['uniqueindex'] = '55100001';
				//生成错误日志
				error_log($errorstr,3,'./Think/Log/error_'.$customer['uniqueindex'].'.log');
			}
			$this->ajaxReturn($data);
		}else{
			$data['msg'] = '手机号非法';
			$this->ajaxReturn($data);
		}
	}


	/**
	 * 验证码验证接口
	 * @param stirng phone : 手机号
	 * @param string code : 验证码
	 * @return json data
	 */
	public function verifyCode(){
		$phone = trim(I('post.phone'));
		$code = trim(I('post.code'));
		$reg =  '/^((13[0-9])|(14[5,7,9])|(15[0-35-9])|(17[0,1,3,5,6,7,8])|(18[0-9]))\d{8}$/';//手机号正则
		$data = array();
		$data['rst'] = '-1';
		$data['msg'] = '手机号或验证码有误';

		if(preg_match($reg,$phone) && $code){
				if($code != $this->User->findCode($phone)){
					$data['msg'] = '验证码错误';
				}else{
					//验证码正确，保存用户注册信息
					$data['rst'] = '0';
					$data['msg'] = '验证码正确';
				}
		}
		$this->ajaxReturn($data);
	}

	/**
	 * 用户手机注册
	 * @param string phone :手机号码
	 * @param string 密码
	 * @return json data
	 */
	public function register(){
		$params = array();
		$params['phone'] = trim(I('post.phone'));
		$params['password'] = trim(I('post.password'));

		$data = array();
		$data['rst'] = '-1';
		if(passStrengthDetect($params['password'])){
			$params['salt'] = getsalt();//生成密码干扰码
			$params['password'] = TransPassUseSalt($params['password'],$params['salt']);//组成新密码

			//刚注册用户默认名称：派生活新用户
			//$params['nickname'] = '派生活新用户';

			//随机生成昵称
			$flag = false;
			$nickname = '';
			while($flag == false){
				$chinese = file_get_contents(dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Files/chinese.txt');
				preg_match_all('/./u',$chinese,$arr);
				$chinesearr = $arr[0];
				$nums = rand(1,6);
				for($i=0;$i<$nums;$i++){
					$word = $chinesearr[rand(0,count($chinesearr))];
					$nickname .= $word;
				}
				$users = $this->User->getAll();
				$flag = true;
				foreach($users as $key => $row){
					if($row['nickname'] == $nickname){
						$flag = false;
					}
				}
			}
			$params['nickname'] = $nickname;
			$params['creatime'] = date('Y-m-d H:i:s');
			$params['belongid'] = C('CUSTOMER_UNIQUE_INDEX');

			//保存注册信息
			$result = $this->User->addUser($params);
			if($result){
				$data['rst'] = '0';
				$data['msg'] = '注册成功';
				$this->User->saveuOperation($result,1);//保存注册记录
				$this->User->updateUserInfo($result,array('localid'=>$result));
			}

		}else{
			$data['msg'] = '密码需为数字和字母的组合，且长度为8~18位';
		}
		$this->ajaxReturn($data);
	}


	/**
	 *用户登入
	 * @param string phone :用户手机
	 * @param string password: 密码
	 * @param json : data
	 */
	public function login(){
		$phone = trim(I('post.phone'));
		$data = array();
		$data['rst'] = '-1';
		$data['msg'] = '用户名或密码错误';
		$user = $this->User->findUser($phone);//通过手机号，直接查找用户
		if($user != null){
			$password = TransPassUseSalt(trim(I('post.password')),$user[0]['salt']);//组合密码
			if($password == $user[0]['password']){
				if($user[0]['status'] == 1){
					$data['msg'] = '该账户已被冻结,无法正常登入';
				}else{
					$data['rst'] = '0';
					$data['msg'] = '登入成功';
					$this->User->login(array('phone'=>$phone));
					$this->User->saveuOperation($user[0]['uid'],2);//保存登入记录
					$use['userid'] = $user[0]['uid'];
					$use['phone'] = $user[0]['phone'];
					$use['username'] = $user[0]['nickname'];
					$use['logo'] = $user[0]['logo'];
					$use['birthday'] = $user[0]['birthday'];
					$data['user'] = $use;
				}
			}
		}

		$this->ajaxReturn($data);
	}

	/**
	 * 账户信息修改(不含密码修改)
	 * @param int userid :用户ID
	 * @param string logo : 头像地址
	 * @param string username : 用户名
	 * @param string phone : 手机号
	 * @param string birthday :生日
	 * @return json: data
	 */
	public function updateUserInfo(){
		$params = array();

		$uid =  I('post.userid',null,'int');
		//头像地址
		$logo =  trim(I('post.logo'));
		if(!empty($logo)){
			$params['logo'] = $logo;
		}
		//用户昵称
		$nickname =  trim(I('post.username'));
		if(!empty($nickname)){
			$params['nickname'] =  $nickname;
		}
		//手机号
		$phone = trim(I('post.phone'));
		if(!empty($phone)){
			$params['phone'] = $phone;
		}
		//生日
		$birthday = trim(I('post.birthday'));
		if(!empty($birthday)){
			$params['birthday'] = $birthday;
		}
		//设置昵称是否显示
		$shownickname = I('post.showname',null,'int');
		if(isset($shownickname)){
			if(!in_array($shownickname,array('0','1'))){
				$shownickname = '1';
			}
			$params['shownickname'] = $shownickname;

		}

		//设置手机号是否显示
		$showphone = I('post.showphone',null,'int');
		if(isset($showphone)){
			if(!in_array($showphone,array('0','1'))){
				$showphone = '1';
			}
			$params['showphone'] = $showphone;
		}

		$data = array();
		$data['rst'] = '-1';
		$data['msg'] = 'failure';

		$result = $this->User->updateUserInfo($uid,$params);
		if($result){
			$data['rst'] = '0';
			$data['msg'] = 'success';
			$this->User->saveuOperation($uid,6);//保存修改个人信息记录
		}

		if($params['nickname']){
			$taskname= $this->UserTask->getUserTask(array('userid'=>$uid,'taskid'=>C('task_name')));
			if($taskname == 0){
				$this->UserTask->saveUserTask(array('userid'=>$uid,'taskid'=>C('task_name')));
				$this->User->updateTotalScore($uid,C('task_score_name'));
				$data['msg'] = '首次修改信息，积分+'.C('task_score_name');
			}
		}

		if($params['birthday']){
			$taskname= $this->UserTask->getUserTask(array('userid'=>$uid,'taskid'=>C('task_birthday')));
			if($taskname == 0){
				$this->UserTask->saveUserTask(array('userid'=>$uid,'taskid'=>C('task_birthday')));
				$this->User->updateTotalScore($uid,C('task_score_birthday'));
				$data['msg'] = '首次修改信息，积分+'.C('task_score_birthday');
			}

		}
		$this->ajaxReturn($data);
	}

	/**
	 * 账户信息获取
	 * @param int userid ：用户ID
	 */
	public function getUserInfo(){
		$uid = I('post.userid',null,'int');
		if(isset($uid)){
			$data = $this->User->getUserInfo($uid);
			unset($data[0]['salt']);
			unset($data[0]['password']);
		}
		$this->ajaxReturn($data);
	}

	/**
	 * 修改密码
	 * @param int userid :用户ID
	 * @param string oldpwd :旧密码
	 * @param string newpwd : 新密码
	 * @return json : data
	 */
	public function changePassword(){
		$uid = I('post.userid',null,'int');
		$newpwd =  trim(I('post.newpwd'));//新密码
		$oldpwd = trim(I('post.oldpwd'));//旧密码

		$data = array();
		$data['rst'] = '-1';
		$data['msg'] = '新密码和旧密码不能相同';

		if(!empty($uid) && !empty($newpwd) && !empty($oldpwd)){
			if(passStrengthDetect($newpwd)){
				$user = $this->User->getUserInfo($uid);
				if($user){
					$password = TransPassUseSalt($oldpwd,$user[0]['salt']);//组合密码
					if($password == $user[0]['password']){
						$newpwd = TransPassUseSalt($newpwd,$user[0]['salt']);//组合密码
						$result = $this->User->updateUserInfo($uid,array('password'=>$newpwd));
						if($result){
							$data['rst'] = '0';
							$data['msg'] = 'success';
							$this->User->saveuOperation($uid,5);//保存修改个人信息记录
						}
					}else{
						$data['msg'] = '旧密码不正确';
					}
				}
			}else{
				$data['msg'] = '密码需为数字和字母的组合，且长度为8~18位';
			}

		}

		$this->ajaxReturn($data);
	}

	/**
	 * 忘记密码
	 * @param string phone:手机号
	 * @param string password:新密码
	 * @return json
	 */
	public function forgetPassword(){
		$phone = trim(I('post.phone'));
		$reg =  '/^((13[0-9])|(14[5,7,9])|(15[0-35-9])|(17[0,1,3,5,6,7,8])|(18[0-9]))\d{8}$/';//手机号正则

		$data = array();
		$data['rst'] = '-1';
		$data['msg'] = '手机号有误';
		if(preg_match($reg,$phone)){
			$userid = $this->User->findUser($phone);
			if($userid){
				$params['password'] = trim(I('post.password'));
				if(passStrengthDetect($params['password'])){
					$params['salt'] = getsalt();//生成密码干扰码
					$params['password'] = TransPassUseSalt($params['password'],$params['salt']);//组成新密码

					//保存注册信息
					$result = $this->User->forgetPassword(array('phone'=>$phone),$params);
					if($result){
						$data['rst'] = '0';
						$data['msg'] = '修改密码成功';
						$this->User->saveuOperation($userid[0]['uid'],51);//保存用户操作记录
					}else{
						$data['msg'] = '系统繁忙，请稍后再试';
					}

				}else{
					$data['msg'] = '密码需为数字和字母的组合，且长度为8~18位';
				}
			}else{
				$data['msg'] = '该手机号不存在，请直接注册使用';
			}
		}

		$this->ajaxReturn($data);
	}

	/**
	 * 上传个人图像
	 * userid int ：用户ID
	 * img : 上传的图像
	 */
	public function uploadImg(){

			$uid = I('post.userid', '', 'int');

			$data['rst'] = '-1';
			$data['msg'] = '上传失败';

			$ret = array();
			$upload = new \Think\Upload();
			$upload->maxSize = C('ITEM_IMG_MAXSIZE');;
			$upload->exts = array('jpg', 'jpeg', 'png', 'gif', 'bmp');
			$upload->rootPath = C('ITEM_IMG_PATH');
			$upload->subName = array('date', 'Ym');
			$upfinfo = $upload->upload();

			if (!$upfinfo) {// 上传错误提示错误信息
				$ret['error'] = true;
				$ret['result'] = $upload->getError();
				//$this->error($upload->getError());
			} else {// 上传成功
				foreach ($upfinfo as $k => &$file) {
					$file['fullpath'] = $upload->rootPath . $file['savepath'] . $file['savename'];
				}
				$ret['error'] = false;
				$ret['result'] = $upfinfo;
			}
			if ($ret['error'] == false) {
				$param['logo'] = $ret['result']['img']['fullpath'];
				$result = $this->User->updateUserInfo($uid,$param);
				if ($result) {
					$data['rst'] = '0';
					$data['msg'] = '上传成功';
					$data['img'] = $param['logo'];
					$this->User->saveuOperation($uid,6);//保存修改个人信息记录
					$taskupload = $this->UserTask->getUserTask(array('userid'=>$uid,'taskid'=>C('task_uploadimg')));
					if($taskupload == 0){
						$this->UserTask->saveUserTask(array('userid'=>$uid,'taskid'=>C('task_uploadimg')));
						$this->User->updateTotalScore($uid,C('task_score_uploadimg'));
						$data['msg'] = '首次上传，积分+'.C('task_score_uploadimg');
					}
				}

			}
			$this->ajaxReturn($data);
	}

	/**
	 * 退出登入
	 * @param int $userid:用户ID
	 * @return json data
	 */
	public function exitLogin(){
		$uid = I('post.userid','','int');
		$result = $this->User->saveuOperation($uid,3);
		if($result){
			$this->ajaxReturn(array('rst'=>'0','msg'=>'退出成功'));
		}else{
			$this->ajaxReturn(array('rst'=>'-1','msg'=>'退出失败'));
		}
	}




}