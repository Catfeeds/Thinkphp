<?php
namespace Api\Controller;
use Think\Controller;

class ProductController extends Controller {

    private $ProductLogic;
    private $User;
    private $Userproduct;
	private $TaskLogic;
	private $SignLogic;

    public function __construct(){
        parent::__construct();
        $this->ProductLogic = D('Product','Logic');
        $this->User = M('User');
        $this->Userproduct = M('User_product');
	    $this->TaskLogic = D('Task','Logic');
	    $this->SignLogic = D('Sign','Logic');
    }

	/**
	 * 获取用户积分
	 * @param int userid:用户ID
	 * @param int type :平台类型 1 ;android 2:IOS 默认为1：android
	 */
    public function GetUserPoint(){/*
        $userid = I('post.userid');
//        $userid = 1001;
        $data = $this->User->where('uid='.$userid)->field('uid,totalscore point,pointtoexpire,expiredpoint')->find();
        $this->ajaxReturn($data);*/

	    $type = I('post.type',null,'int');
	    if(!in_array($type,array(1,2))){
		    $type = 1;
	    }

	    $userid = I('post.userid',null,'int');
	    if($userid){
		    $ad = $this->User->where('uid='.$userid)->find();
		    if($ad){
			    $userTask = $this->TaskLogic->getTaskList($userid,1,1,1,2);
			    if($userTask){
				    $usertask = $userTask['list'];
				    $tomorrowscore = null;
				    $datefrom = date('Y-m-d 00:00:00');
				    $dateto = date('Y-m-d 00:00:00',strtotime('+1 day'));
				    $wh['signtimes'] = array('between',"$datefrom,$dateto");
				    $wh['userid'] = $userid;
				    $todaysign = $this->SignLogic->getSignRecordByUid($wh);
				    $datefrom1 = date('Y-m-d 00:00:00',strtotime('-1 day'));
				    $dateto1 = date('Y-m-d 00:00:00');
				    $wh['signtimes'] = array('between',"$datefrom1,$dateto1");
				    $yesterdaysign = $this->SignLogic->getSignRecordByUid($wh);
				    $ss =  date('Y-m-01');
				    $lastday = date('d',strtotime("$ss +1 month -1 day"));
				    $nowday = date('d');
				    foreach($usertask as $key=>$value){
					    if($value['categoryid'] == C('task_rc')){
							if($value['taskid'] == C('task_fx') || $value['taskid'] == C('task_qd')){
								$tomorrowscore += $value['score'];
							}else if($value['status'] == 0 && $value['taskid'] == C('sign_3')){
								if($todaysign && $nowday<$lastday){
									if($todaysign[0]['days'] == 2){
										$tomorrowscore += $value['score'];
									}
								}
							}else if($value['status'] == 0 && $value['taskid'] == C('sign_7')){
								if($todaysign && $nowday<$lastday){
									if($todaysign[0]['days'] == 6){
										$tomorrowscore += $value['score'];
									}
								}
							}else if($value['status'] == 0 && $value['taskid'] == C('sign_15')){
								if($todaysign && $nowday<$lastday){
									if($todaysign[0]['days'] == 14){
										$tomorrowscore += $value['score'];
									}
								}
							}else if($value['status'] == 0 && $value['taskid'] == C('sign_max')){
								if($todaysign && $nowday<$lastday){
									if($todaysign[0]['days'] == ($lastday-1)){
										$tomorrowscore += $value['score'];
									}
								}
							}
					    }else if($value['categoryid'] ==  C('task_xs')){
							if($value['status'] == 0){
								$tomorrowscore += $value['score'];
							}
					    }else if($value['categoryid'] ==  C('task_yy') && $type == 1){
						    if($value['status'] == 0){
							    $tomorrowscore += $value['score'];
						    }
					    }
		            }


				    $data['userid'] = $ad['uid'];
				    $data['point'] = $ad['totalscore'];
				    $data['tomorrowscore'] = $tomorrowscore;
				    $data['pointtoexpire'] = $ad['pointtoexpire'];
				    $data['expiredpoint'] = $ad['expiredpoint'];
			    }else{
				    $data['rst'] = -1;
				    $data['msg'] = '系统异常，请稍后尝试';
			    }
		    }else{
			    $data['rst'] = -1;
			    $data['msg'] = '系统异常，请稍后尝试';
		    }
	    }else{
		    $data['rst'] = -1;
		    $data['msg'] = '传递参数有误';
	    }
	    $this->ajaxReturn($data);
    }

    public function productList(){
        $userid = I('post.userid');
//        $userid = 1001;
        $data = $this->ProductLogic->getProductList($userid);
        $this->ajaxReturn($data);
    }

   public function GetProductDetail(){
        $productid = I('post.productid',null,'int');
	   if(!isset($productid)){
		   $retu['ret'] = -1;
		   $retu['msg'] = '参数不合法';
		   $this->ajaxReturn($retu);
	   }

//        $productid = 1011;
        $data = $this->ProductLogic->getProductById($productid);
        $this->ajaxReturn($data);
    }


	/**
    public function ExchangePoint(){
        $userid = I('post.userid');
        $productid = I('post.productid');
//        $userid = 1001;
//        $productid = 1013;
        $nums = $this->Userproduct->where(array('userid'=>$userid,'productid'=>$productid))->count();
        if($nums){
            $data['rst'] = 0;
            $data['msg'] = 'This Product has exchanged';
        }else{
            $data = $this->ProductLogic->exchangeProductByScore($userid,$productid);
            $data['rst'] = 0;
            $data['msg'] = 'Exchange good is success';
        }
        $this->ajaxReturn($data);
    }
	 * */

   /** public function GetPointProducts(){
        $userid = I('post.userid');
        $searchstring = I('post.keywords');
//        $userid = 1001;
//        $searchstring = '苹果';
        $data = $this->ProductLogic->searchProductByString($userid,$searchstring);
        $this->ajaxReturn($data);
    }
    * */

	/**
	 * 积分兑换
	 * 参数：
	 * @param int userid : 用户ID
	 * @param int productid : 产品ID
	 */
	public function exchangePoint(){
		$params = array();

		//用户ID
		$params['userid'] = I('post.userid',null,'int');
		//产品ID
		$params['productid'] = I('post.productid',null,'int');

		if(!isset($params['userid']) || !isset($params['productid'])){
			$retu['rst'] = '-1';
			$retu['msg'] = '参数不合法';
			$this->ajaxReturn($retu);
		}

		$data = $this->User->field('totalscore')->where('uid='.$params['userid'])->select();
		$userscore = $data[0]['totalscore'];//用户拥有积分
		$data = $this->ProductLogic->getProductById($params['productid']);
		$productscore = $data[0]['point'];//该商品所需要的积分
		$productnum = $data[0]['amount'];//该产品剩余数量

		$retu = array();//用于返回
		if($productnum<1){
			$retu['rst'] = '-1';
			$retu['msg'] = '库存不足，兑换失败';
		}else if($productscore>$userscore){
			$retu['rst'] = '-1';
			$retu['msg'] = '您的积分不能满足该兑换要求';
		}else{
			$data = $this->ProductLogic->exchangePoint($params,$userscore,$productnum,$productscore);
			$retu['recordid'] = $data;
			$retu['rst'] = '0';
			$retu['msg'] = '兑换成功';
		}
		$this->ajaxReturn($retu);

	}

	/**
	 * 保存用户收货地址
	 * @param recordid :int 兑换成功是返回的ID
	 * @param int  userid ：用户ID
	 * @param string username: 姓名
	 * @param string phone :手机号
	 * @param string province:省份
	 * @param string city: 城市
	 * @param string district:区
	 * @param string address:详细地址
	 * @param string postcode:邮政编码
	 */
	public function addAddress(){
		$userid =  I('post.userid',null,'string');
		$recordid =  I('post.recordid',null,'int');
		$username = I('post.username',null,'string');
		$phone =  I('post.phone',null,'string');
		$province =  I('post.province',null,'string');
		$city = trim(I('post.city',null,'string'));
		$district =  trim(I('post.district',null,'string'));
		$address =  trim(I('post.address',null,'string'));
		$postcode = trim(I('post.postcode',null,'string'));

		$data['rst'] = -1;
		if(empty($userid) || empty($recordid) || empty($username) || empty($phone) || empty($province) || empty($city) || empty($district) || empty($address)){
			$data['msg'] = '参数不合法';
			$this->ajaxReturn($data);
		}else{
			$reg =  '/^((13[0-9])|(147)|(15[0-35-9])|(17[7,8])|(18[0-35-9]))\d{8}$/';
			if(preg_match($reg,$phone)){
				$conds['userid'] = $userid;
				$conds['username'] = $username;
				$conds['phone'] = $phone;
				$conds['province'] = $province;
				$conds['city'] = $city;
				$conds['district'] = $district;
				$conds['postcode'] = $postcode;
				$conds['address'] = $address;
				$result = $this->ProductLogic->addAddress($conds,$recordid);
				if($result){
					$data['rst'] = 0;
					$data['msg'] = '操作成功';
				}else{
					$data['msg'] = '系统异常，请稍后再试';
				}
				$this->ajaxReturn($data);
			}else{
				$data['mag'] = '手机号有误，请重新输入';
				$this->ajaxReturn($data);
			}
		}
	}



	/**
	 * 获取商城商品（含搜索）
	 * 参数：
	 * @param int producttypeid : （可选）产品类型：1：虚拟，2：实物  默认所有
	 * @param string keywords   : (可选)搜索关键字匹配产品名，描述
	 * @oaram int id : (可选)产品ID （用于获取产品详情）
	 * @param int rowcount :每页返回记录数量
	 * @param int pages : 当前页数（默认为1）
	 * @param int number: 返回记录数量
	 * @param return json data
	 */
	public function getPointProducts(){
		$params = array();

		//产品类型
		$categoryid = I('post.producttypeid',null,int);
		if($categoryid == 1){
			$params['categoryid'] = '50'; //虚拟产品
		}else if($categoryid == 2){
			$params['categoryid'] = '51'; //实物产品
		}

		//搜索关键字
		$keywords = trim(I('post.keywords'));
		if(!empty($keywords)){
			$param['productname'] =  array('like','%'.$keywords.'%');
			$param['content'] =  array('like','%'.$keywords.'%');
			$param['_logic'] = 'or';
			$params['_complex'] = $param;
		}

		//产品id
		$id = I('post.productid',null,'int');
		if(isset($id)){
			$params['id'] = $id;
		}

		$number = I('post.number',null,'int')?I('post.number',null,'int'):10000;

		$params['isonline'] = '0';//上架状态
		$pages = I('post.pages','','int')?I('post.pages','','int'):1;
		$rowcount = I('post.rowcount','','int')?I('post.rowcount','','int'):C('MOB_REC_PER_PAGE');

		$data =  $this->ProductLogic->getPointProducts($params,$pages,$rowcount,$number);
		$this->ajaxReturn($data);
	}

	/**
	 *积分兑换记录
	 * @param int userid:用户ID
	 * @param int pages:（可选）分页 默认为1
	 * @parms int rowcount:（可选）每页返回记录数
	 * @return json data
	 */
	public function exchangeRecord(){
		$userid = I('post.userid',null,'int');
		$pages = I('post.pages','','int')?I('post.pages','','int'):1;
		$rowcount = I('post.rowcount','','int')?I('post.rowcount','','int'):C('MOB_REC_PER_PAGE');

		if(isset($userid)){
			$params['userid'] =  $userid;
			$data =  $this->ProductLogic->exchangeRecord($params,$pages,$rowcount);
		}else{
			$data['rst'] = -1;
			$data['msg'] = '无效的USERID';
		}

		$this->ajaxReturn($data);
	}

	/**
	 * 热门积分兑换商品
	 * @param int pages :(可选)分页 默认为1
	 * @param int rowcount :(可选)每页返回记录数，默认系统设置值
	 * @param json data
	 */
	public function getHotProducts(){
		$pages = I('post.pages','','int')?I('post.pages','','int'):1;
		$rowcount = I('post.rowcount','','int')?I('post.rowcount','','int'):C('MOB_REC_PER_PAGE');
		$data = $this->ProductLogic->getHotProducts($pages,$rowcount);

		$this->ajaxReturn($data);
	}

	/**
	 * 获取收货地址
	 * @param int addressid :地址ID
	 */
	public function getAddress(){
		$id = I('post.addressid',null,'int');
		if(isset($id)){
			$data =  $this->ProductLogic->getAddress($id);
			if($data){
				$this->ajaxReturn($data);
			}else{
				$this->ajaxReturn(array('msg'=>'系统繁忙，请稍后再试','rst'=>-1));
			}
		}else{
			$data['msg'] = '参数不合法';
			$data['rst'] = -1;
			$this->ajaxReturn($data);
		}
	}

}