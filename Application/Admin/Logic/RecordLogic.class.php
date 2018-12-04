<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Admin\Logic;


class RecordLogic extends \Think\Model{

	private $Customer;
	private $Boperation;
	private $AppCateView;
	private $Category;
	private $VideoDataView;
	private $User;
	private $UserDataView;
	private $Model;
	private $Uoperation;
	private $UserLoginView;

	public function __construct(){
		$this->User = M('User');
		$this->Customer = M('Customer');
		$this->Boperation = M('Boperation');
		$this->AppCateView = D('AppCateDateView');
		$this->Category = D('Category','Logic');
		$this->VideoDataView =  D('VideoDataView');
		$this->UserDataView = D('UserDataView');
		$this->Model = M();
		$this->Uoperation = M('Uoperation');
		$this->UserLoginView = D('UserLoginView');
	}

	/**
	 * @param $id
	 * @param $mon
	 * @return array
	 * 厂区播放下载总数据读取
	 */
	public function customerData($id,$mon){
		$customer = $this->getCustomer($id);

		$datefrom = date('Y').'-'.$mon.'-01';
		$dateto = date('Y-m-d',strtotime("$datefrom +1 month -1 day"));

		$wh['creatime'] = array('between',"$datefrom,$dateto");
		$wh['factory'] = $customer['uniqueindex'];
		$wh['operation'] = '1';

		$viewdata =  $this->Boperation->field("DATE_FORMAT(creatime,'%d') newday,count(*) as total")->where($wh)->group('newday')->order('newday')->select();

		$wh['operation'] = '2';
		$downloaddata = $this->Boperation->field("DATE_FORMAT(creatime,'%d') newday,count(*) as total")->where($wh)->group('newday')->order('newday')->select();

		return(array_merge(array('view'=>$viewdata),array('download'=>$downloaddata)));

	}

	/**
	 * @param $id
	 * @param $mon
	 * @return array
	 * 厂区下载数据读取
	 */
	public function categoryData($id,$mon){
		$customer = $this->getCustomer($id);

		$datefrom = date('Y').'-'.$mon.'-01';
		//$datefrom = date('Y').'-'.$mon.'-01';
		$dateto = date('Y-m-d',strtotime("$datefrom +1 month -1 day"));

		$categoryres = $this->Category->getParentCategoryList(C('GAMES_CATEGORY'));
		$category = array();
		foreach($categoryres as $key=>$value){
			$category[] = $value['name'];
		}

		$wh['creatime'] = array('between',"$datefrom,$dateto");
		$wh['factory'] = $customer['uniqueindex'];
		$wh['operation'] = '2';

		$appcate = $this->AppCateView->field("tags,tagsid,creatime,factory,operation,count(*) total")->where($wh)->group('tagsid')->select();

		return(array_merge(array('category'=>$category),array('appcate'=>$appcate)));
	}

	/**
	 * @param $id
	 * @return mixed
	 * 根据厂区id获取厂区客户信息
	 */
	public function getCustomer($id){
		$customer = $this->Customer->where(array('id'=>$id))->find();
		return $customer;
	}

	/**
	 * @param $id
	 * @param $p
	 * @return array
	 * 厂区播放数据读取
	 */
	public function videoStatistic($id,$p,$wh=array()){
		$pages = $p.','.C('ADMIN_REC_PER_PAGE');
		$customer = $this->getCustomer($id);

		$datefrom  = date('Y-m-01');
		$dateto = date('Y-m-d',strtotime("$datefrom +1 month -1 day"));

		if(count($wh) == 0){
			$wh['creatime'] = array('between',"$datefrom,$dateto");
		}
		$wh['factory'] = $customer['uniqueindex'];
		$wh['operation'] = '1';

		$data = $this->VideoDataView->field('id,name,category,count(*) total')->where($wh)->group('objid')->order('total desc')->page($pages)->select();
		$total = $this->Boperation->where($wh)->count('DISTINCT objid');

		if($data === false || $total === false){
			$this->errer('系统繁忙，请稍后再试');
		}else{
			return(array_merge(array('data'=>$data),array('total'=>$total)));
		}

	}

	/**
	 * 厂区用户数据读取
	 */
	public function userStatistic($id,$p,$datefrom=0,$dateto=0){
		//$pages = $p.','.C('ADMIN_REC_PER_PAGE');
		$customer = $this->getCustomer($id);

		if($datefrom == 0 || $dateto == 0){
			$datefrom  = date('Y-m-01');
			$dateto = date('Y-m-d',strtotime("$datefrom +1 month -1 day"));
		}
		$wh['creatime'] = array('between',"$datefrom,$dateto");

		$wh['belongid'] = $customer['uniqueindex'];
		$belongid = $customer['uniqueindex'];

		$total = $this->User->field('id')->where($wh)->count();

		//$data = $this->User->field(true)->where($wh)->order('creatime desc')->page($pages)->select();
		//$wh['operation'] = '2';
		//$data = $this->UserDataView->where($wh)->order('ucreatime desc')->group('uid')->page($pages)->select();

		$pages = ($p-1)*C('ADMIN_REC_PER_PAGE');
		$num = C('ADMIN_REC_PER_PAGE');
		$data = $this->Model->query("select aa.uid,aa.status,aa.lastlogindate,aa.lastloginip,aa.username,aa.nickname,aa.password,aa.totalscore,aa.pointtoexpire,aa.expiredpoint,aa.email,aa.phone,aa.sex,aa.logo,aa.creatime,aa.birthday,aa.salt,aa.shownickname,aa.showphone,aa.belongid,aa.localid,bb.operation,bb.creatime ucreatime
										from __USER__ aa left join
										(SELECT a.id,a.userid,a.creatime,a.operation,a.factory,a.tags
										FROM __UOPERATION__ a
										LEFT JOIN __UOPERATION__ b ON a.userid=b.userid AND a.creatime<b.creatime
										where  a.operation=2  group by a.userid,a.creatime,a.operation
										having count(b.id)<1 ORDER BY a.creatime desc) as bb
										on aa.localid=bb.userid
										where  aa.belongid=$belongid and aa.creatime BETWEEN '$datefrom' and '$dateto' order by aa.creatime desc limit $pages,$num");
		foreach($data as $key=>$value){
			if(empty($value['ucreatime'])){
				$data[$key]['num'] = '从未登入';
			}else{
				$datenum  = strtotime($value['ucreatime']);
				$nownum = strtotime(date('Y-m-d 00:00:00'));
				$nologintime = round(($nownum-$datenum)/3600);
				if($nologintime < 24){
					$data[$key]['num'] = $nologintime.'小时';
				}else{
					$day = floor($nologintime/24);
					$house = $nologintime%24;
					$data[$key]['num'] = $day.'天'.$house.'小时';
				}

			}

		}


		if($data === false || $total === false){
			$this->errer('系统繁忙，请稍后再试');
		}else{
			return(array_merge(array('data'=>$data),array('total'=>$total)));
		}

	}

	/**
	 * 不区分厂区总播放数据
	 */
	public function videoData($mon=0,$p){
		$pages = $p.','.C('ADMIN_REC_PER_PAGE');
		$wh['operation'] = '1';

		if($mon != 0){
			$datefrom = date('Y').'-'.$mon.'-01';
			//$datefrom = date('Y').'-'.$mon.'-01';
			$dateto = date('Y-m-d',strtotime("$datefrom +1 month -1 day"));
			$wh['creatime'] = array('between',"$datefrom,$dateto");
		}

		$data = $this->VideoDataView->field('id,name,category,categoryname,count(*) total')->where($wh)->group('objid')->order('total desc')->page($pages)->select();
		$total = $this->Boperation->where($wh)->count('DISTINCT objid');
		if($data === false || $total === false){
			$this->errer('系统繁忙，请稍后再试');
		}else{
			return(array_merge(array('data'=>$data),array('total'=>$total)));
		}

	}

	/**
	 * @param $id
	 * @@return array
	 * 厂区用户登入明细
	 */
	public function userLoginData($id,$p,$wh=array()){
		$pages = $p.','.C('ADMIN_REC_PER_PAGE');
		$customer = $this->getCustomer($id);

		$datefrom  = date('Y-m-01');
		$dateto = date('Y-m-d',strtotime("$datefrom +1 month -1 day"));

		if(count($wh) == 0){
			$wh['creatime'] = array('between',"$datefrom,$dateto");
		}
		$wh['factory'] = $customer['uniqueindex'];
		$wh['operation'] = '2';

		//$data = $this->Uoperation->field(true)->where($wh)->order('creatime desc')->page($pages)->select();
		$data =  $this->UserLoginView->field(true)->where($wh)->order('creatime desc')->page($pages)->select();
		$total = $this->Uoperation->where($wh)->count();

		if($data === false || $total === false){
			$this->errer('系统繁忙，请稍后再试');
		}else{
			return(array_merge(array('data'=>$data),array('total'=>$total)));
		}

	}

	/**
	 * 错误日志统计
	 */
	public function errorLogs($id,$p,$wh = array()){
		$customer = $this->getCustomer($id);
		$wh['belongid'] = $customer['uniqueindex'];
		$logarr = array();
		if( isset( $wh['belongid'] ) ) {
			//读取日志内容
			$file = file('./Think/Log/error_'. $wh['belongid'] .'.log');
			if ($file) {
				$cot = 0;
				foreach ($file as $line) {
					if (trim($line) != '') {
						$pos1 = strpos($line, '[');
						$pos2 = strpos($line, ']');
						$date = substr($line, $pos1 + 1, $pos2 - pos1 - 1);
						$logarr[$cot]['date'] = $date;
						$logarr[$cot]['error'] = $line;
						$cot++;
					}
				}
			}
		}

		//根据时间筛选
		$temparr = array();
		if( isset($wh['datefrom']) && isset($wh['dateto']) ){
			foreach ($logarr as $key => $row ) {
				if ( $row['date'] > $wh['datefrom'] && $row['date'] < $wh['dateto'] ) {
					$temparr[] = $logarr[$key];
				}
			}
			$logarr = $temparr;
		}

		//根据时间从大到小排序
		for( $i=0; $i<count($logarr)-1; $i++ ){
			for( $j=0; $j<count($logarr)-$i-1; $j++ ){
				if( $logarr[$j] < $logarr[$j+1] ){
					$temp = $logarr[$j+1];
					$logarr[$j+1] = $logarr[$j];
					$logarr[$j] = $temp;
				}
			}
		}

		//读取详细信息
		//Example: [2016-07-14 16:07:39]注册新用户，发送短信失败.Error code is:1,Error message is:请求参数缺失,Error detail is:参数 mobile 必须传入.
		$errorarr = array();
		foreach($logarr as $key => $row){
			$errorarr[$key]['date'] = $row['date'];
			$arr1 = explode('.',$row['error']);
			$arr2 = explode(',',$arr1[1]);
			foreach($arr2 as $row2){
				$arr3 = explode(':',$row2);
				if( $arr3[0] == 'Error code is' ){
					$errorarr[$key]['code'] = $arr3[1];
				}elseif( $arr3[0] == 'Error message is' ){
					$errorarr[$key]['msg'] = $arr3[1];
				}elseif( $arr3[0] == 'Error detail is' ){
					$errorarr[$key]['detail'] = $arr3[1];
				}
			}
			$cot++;
		}

		//数据总数
		$errornums = count($errorarr);

		//分页
		$temparr = array();
		for( $i=($p-1)*C('ADMIN_REC_PER_PAGE'); $i<($p-1)*C('ADMIN_REC_PER_PAGE')+C('ADMIN_REC_PER_PAGE'); $i++){
			if( isset($errorarr[$i]) ){
				$temparr[] = $errorarr[$i];
			}
		}
		$errorarr = $temparr;
		$errorarr['total'] = $errornums;

		return $errorarr;
	}

}