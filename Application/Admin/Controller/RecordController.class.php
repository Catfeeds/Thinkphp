<?php
namespace Admin\Controller;
use Think\Controller;
class RecordController extends Controller {

	public function __construct(){
		parent::__construct();
		$this->RecordLogic=  D('Record','Logic');
	}

	private function checkPriv($priv){
		$adminid = session('adminid');
		if(empty($adminid)) $this->redirect('Adminuser/login',0);
		if(!session('issuper')){
			if(!empty($priv) && !in_array($priv,session('privs'))) $this->error('您没有此权限!.');
		}
		$this->assign('adname', session('name'));
	}

	public function index(){
		$this->checkPriv('3_0');
		$this->display();
	}

	private $RecordLogic;

	/**
	 * 厂区播放下载按日总数据
	 */
	public function customerData(){
		$id = I('post.id',null,'int');
		$mon =  I('post.month',null,'int');

		if($mon<10){
			$mon = '0'.$mon;
		}

		$data =  $this->RecordLogic->customerData($id,$mon);
		$viewdata = $data['view'];
		$viewdata1 = array();
		$downloaddata = $data['download'];
		$downloaddata1 = array();

		$max = (int)date('d');
		if((int)$mon != (int)date('m')){
			$datefrom = date('Y').'-'.$mon.'-01';
			$max = date('d',strtotime("$datefrom +1 month -1 day"));
		}


		$newarray = array();
		$totalvideo = 0;
		for($i=1; $i<=$max; $i++){
			$flag = false;
			foreach($viewdata as $v){
				if((int)$v['newday'] == $i){
					//$newarray[] = $v;
					$newarray[] = array('newday'=>$i,'total'=>$v['total']);
					$totalvideo += $v['total'];
					$flag = true;
					break;
				}
			}
			if(!$flag){$newarray[] = array('newday'=>$i,'total'=>0);}
		}

		foreach($newarray as $val){
			$viewdata1[] = (int)$val['total'];
		}

		$newarray = array();
		$totaldownload = 0;
		for($i=1; $i<=$max; $i++){
			$flag = false;
			foreach($downloaddata as $v){
				if((int)$v['newday'] == $i){
					//$newarray[] = $v;
					$newarray[] = array('newday'=>$i,'total'=>$v['total']);
					$totaldownload += $v['total'];
					$flag = true;
					break;
				}
			}
			if(!$flag){$newarray[] = array('newday'=>$i,'total'=>0);}
		}

		foreach($newarray as $val){
			$downloaddata1[] = (int)$val['total'];
		}

		$this->ajaxReturn(array_merge(array('view'=>$viewdata1),array('app'=>$downloaddata1),array('totaldownload'=>$totaldownload),array('totalvideo'=>$totalvideo)));
	}

	/**
	 * 下载分类统计
	 */
	public function categoryData(){
		$id = I('post.id',null,'int');
		$mon =  I('post.month',null,'int');

		if($mon<10){
			$mon = '0'.$mon;
		}
		$data =  $this->RecordLogic->categoryData($id,$mon);

		$catestat = array();
		$totaldownload = 0;
		foreach($data['category'] as $key=>$value){
			$flag = false;
			foreach($data['appcate'] as $key1=>$value1){
				if($value == $value1['tags']){
					$flag = true;
					$catestat[$key] = $value1['total'];
					$totaldownload += $value1['total'];
					break;
				}
			}
			if(!$flag){
				$catestat[$key] = 0;
			}
		}
		$data['appcate'] = $catestat;
		$data['totaldownload'] = $totaldownload;
		$this->ajaxReturn($data);

	}

	/**
	 * 视频播放数据统计
	 */
	public function videoStatistic(){
		$this->checkPriv('12_0');

		$id = I('get.id',null,'int');
		$datefrom = I('get.start_time',null,'string');
		$dateto = I('get.end_time',null,'string');

		$p = getCurPage();
		$wh = array();
		if(isset($datefrom) && isset($dateto)){
			$wh['creatime'] = array('between',"$datefrom,$dateto");
		}

		$result = $this->RecordLogic->videoStatistic($id,$p,$wh);
		$this->assign('id',$id);
		$this->assign('start_time',$datefrom);
		$this->assign('end_time',$dateto);
		$this->assign('data',$result['data']);
		$this->total = $result['total'];

		//$show = $result['show'];

		$show = constructAdminPage($this->total);
		$this->assign('page',$show);

		$this->display("Customer/customervideomgr");
	}

	/**
	 * 厂区用户查看
	 */
	public function userStatistic(){
		$this->checkPriv('12_0');

		$id = I('get.id',null,'int');
		$datefrom = I('get.start_time',null,'string');
		$dateto = I('get.end_time',null,'string');

		$p = getCurPage();
		/*$wh = array();
		if(isset($datefrom) && isset($dateto)){
			$wh['creatime'] = array('between',"$datefrom,$dateto");
		}*/

		$result = $this->RecordLogic->userStatistic($id,$p,$datefrom,$dateto);
		//dump($result);exit;
		$this->assign('id',$id);
		$this->assign('start_time',$datefrom);
		$this->assign('end_time',$dateto);
		$this->assign('data',$result['data']);
		$this->total = $result['total'];
		$show = constructAdminPage($this->total);
		$this->assign('page',$show);

		$this->display("Customer/customerusermgr");
	}

	/**
	 * 不区分厂区视频总播放次数
	 */
	public function videoData(){
		$mon =  I('post.month',null,'int');
		$p = getCurPage();
		$result = $this->RecordLogic->videoData($mon,$p);
		$this->assign('data',$result['data']);
		$this->total = $result['total'];
		$show = constructAdminPage($this->total);
		$this->assign('page',$show);

		$this->display("Customer/videodata");

	}

	/**
	 * 厂区用户登入明细
	 */
	public function userLoginData(){
		$this->checkPriv('12_0');

		$id = I('get.id',null,'int');
		$datefrom = I('get.start_time',null,'string');
		$dateto = I('get.end_time',null,'string');

		$p = getCurPage();
		$wh = array();
		if(isset($datefrom) && isset($dateto)){
			$wh['creatime'] = array('between',"$datefrom,$dateto");
		}

		$result = $this->RecordLogic->userLoginData($id,$p,$wh);
		$this->assign('id',$id);
		$this->assign('start_time',$datefrom);
		$this->assign('end_time',$dateto);
		$this->assign('data',$result['data']);
		$this->total = $result['total'];

		//$show = $result['show'];

		$show = constructAdminPage($this->total);
		$this->assign('page',$show);

		$this->display("Customer/userlogindata");
	}

	/**
	 * 错误日志
	 */
	public function getErrorLog(){
		$this->checkPriv('12_0');

		$id = I('get.id',null,'int');
		$datefrom = I('get.start_time',null,'string');
		$dateto = I('get.end_time',null,'string');

		$p = getCurPage();
		$wh = array();
		if(isset($datefrom) && isset($dateto)){
			$wh['datefrom'] = $datefrom;
			$wh['dateto'] = $dateto;
		}

		$result = $this->RecordLogic->errorLogs($id,$p,$wh);

		$this->assign('id',$id);
		$this->assign('start_time',$datefrom);
		$this->assign('end_time',$dateto);
		$this->assign('data',$result);
		$this->total = $result['total'];
		$show = constructAdminPage($this->total);
		$this->assign('page',$show);
		$this->display("Customer/errorlogmgr");
	}

}